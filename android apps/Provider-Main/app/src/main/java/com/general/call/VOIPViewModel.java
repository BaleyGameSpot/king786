package com.general.call;

import static org.webrtc.SessionDescription.Type.OFFER;

import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import com.general.files.Media;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.VoipActivityBinding;
import com.model.SocketEvents;
import com.service.handler.AppService;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import org.webrtc.AudioSource;
import org.webrtc.AudioTrack;
import org.webrtc.Camera1Enumerator;
import org.webrtc.Camera2Enumerator;
import org.webrtc.CameraEnumerator;
import org.webrtc.CameraVideoCapturer;
import org.webrtc.DataChannel;
import org.webrtc.DefaultVideoDecoderFactory;
import org.webrtc.DefaultVideoEncoderFactory;
import org.webrtc.EglBase;
import org.webrtc.IceCandidate;
import org.webrtc.MediaConstraints;
import org.webrtc.MediaStream;
import org.webrtc.PeerConnection;
import org.webrtc.PeerConnectionFactory;
import org.webrtc.RtpReceiver;
import org.webrtc.SdpObserver;
import org.webrtc.SessionDescription;
import org.webrtc.SurfaceTextureHelper;
import org.webrtc.VideoSource;
import org.webrtc.VideoTrack;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;
import java.util.Timer;
import java.util.TimerTask;

public class VOIPViewModel {

    private final String TAG;
    private final VOIPActivity act;
    private final VoipActivityBinding binding;

    private final MediaDataProvider dataProvider;
    public boolean isIncomingView, isAnswerBtnClick = false, isCallStart = false;

    private final Media mAudioPlayer;
    private final Timer mTimer = new Timer();
    private long countDownTimer = 0;

    ////
    private EglBase rootEglBase;
    private PeerConnectionFactory mFactory;
    @Nullable
    private PeerConnection peerConnection;
    private CameraVideoCapturer videoCaptureAndroid;
    private VideoTrack localVideoTrack;
    private AudioTrack localAudioTrack;
    private VideoSource videoSource;
    private AudioSource audioSource;
    private AppRTCAudioManager audioManager;
    private MediaStream mMediaStream;

    private final ArrayList<String> iceCandidateList = new ArrayList<>();

    public VOIPViewModel(@NonNull VOIPActivity act, VoipActivityBinding binding, MediaDataProvider dataProvider, boolean isIncomingView) {
        this.act = act;
        TAG = act.getClass().getSimpleName();
        this.binding = binding;
        this.dataProvider = dataProvider;
        this.isIncomingView = isIncomingView;

        mAudioPlayer = new Media(act);
        if (isIncomingView) {
            Media.DEFAULT_TONE = "android.resource://" + act.getPackageName() + "/" + R.raw.phone_loud1;
            mAudioPlayer.playRingtone();
        } else {
            Media.PROGRESS_TONE = R.raw.progress_tone;
            mAudioPlayer.playProgressTone();
        }

        audioManager = AppRTCAudioManager.create(act);

        webrtcInitialize();

        mTimer.schedule(new TimerTask() {
            @Override
            public void run() {
                act.runOnUiThread(() -> {
                    if (countDownTimer > 0) {
                        if (isCallStart) {
                            long totalSeconds = (System.currentTimeMillis() - countDownTimer) / 1000;
                            long minutes = totalSeconds / 60;
                            long seconds = totalSeconds % 60;
                            binding.callTimeTxt.setText(String.format(Locale.ENGLISH, "%02d:%02d", minutes, seconds));
                        } else {
                            long seconds = ((System.currentTimeMillis() - countDownTimer) / 1000) % 60;
                            if (seconds > 30) {
                                binding.declineArea.performClick();
                            }
                        }
                    }
                });
            }
        }, 1000, 1000);
    }

    //------------- ------------ ----------------- ----------
    private void webrtcInitialize() {
        rootEglBase = EglBase.create();

        binding.sfLocalView.init(rootEglBase.getEglBaseContext(), null);
        binding.sfLocalView.setEnableHardwareScaler(true);
        binding.sfLocalView.setMirror(true);
        binding.sfLocalView.setZOrderMediaOverlay(true);

        binding.sfRemoteView.init(rootEglBase.getEglBaseContext(), null);
        binding.sfRemoteView.setEnableHardwareScaler(true);
        binding.sfRemoteView.setMirror(true);

        //initialize peer connection factory
        PeerConnectionFactory.InitializationOptions initializationOptions = PeerConnectionFactory.InitializationOptions.builder(act)
                .setEnableInternalTracer(true)
                .setFieldTrials("WebRTC-H264HighProfile/Enabled/")
                .createInitializationOptions();
        PeerConnectionFactory.initialize(initializationOptions);

        PeerConnectionFactory.Options options = new PeerConnectionFactory.Options();
        options.disableEncryption = false;
        options.disableNetworkMonitor = true;

        mFactory = PeerConnectionFactory.builder()
                .setOptions(options)
                .setVideoDecoderFactory(new DefaultVideoDecoderFactory(rootEglBase.getEglBaseContext()))
                .setVideoEncoderFactory(new DefaultVideoEncoderFactory(rootEglBase.getEglBaseContext(), true, true))
                .createPeerConnectionFactory();

        peerConnection = mFactory.createPeerConnection(setIceSeverData(new ArrayList<>()), new PeerConnection.Observer() {

            @Override
            public void onIceCandidate(IceCandidate iceCandidate) {
                Logger.d(TAG, "Ice >> " + iceCandidate.toString());
                try {
                    JSONObject object = new JSONObject();
                    object.put("type", "candidate");
                    object.put("sdpMLineIndex", iceCandidate.sdpMLineIndex);
                    object.put("sdpMid", iceCandidate.sdpMid);
                    object.put("sdp", iceCandidate.sdp);
                    sendRTCData(dataProvider, object.toString(), 0);
                    if (!isIncomingView) {
                        iceCandidateList.add(object.toString());
                    }
                } catch (Exception e) {
                    Logger.e(TAG, "onIceCandidate | JSONException" + e.getMessage());
                }
            }

            @Override
            public void onAddStream(MediaStream mediaStream) {
                Logger.d(TAG, "onAddStream ::" + mediaStream.getId());
                mMediaStream = mediaStream;
                act.runOnUiThread(() -> {
                    if (audioManager != null) {
                        audioManager.init();
                    }
                    onCallEstablished();

                    if (!mediaStream.audioTracks.isEmpty()) {
                        AudioTrack remoteAudioTrack = mediaStream.audioTracks.get(0);
                        remoteAudioTrack.setEnabled(true);
                    }
                    if (dataProvider.isVideoCall) {
                        binding.sfRemoteView.setVisibility(View.VISIBLE);
                        final VideoTrack videoTrack = mediaStream.videoTracks.get(0);
                        videoTrack.addSink(binding.sfRemoteView);
                        videoTrack.setEnabled(true);
                    } else {
                        binding.sfRemoteView.setVisibility(View.GONE);
                    }
                });
            }

            @Override
            public void onSignalingChange(PeerConnection.SignalingState signalingState) {
                //Logger.d(TAG, "onSignalingChange :: " + signalingState.toString());
            }

            @Override
            public void onIceConnectionChange(PeerConnection.IceConnectionState iceConnectionState) {
                //Logger.d(TAG, "onIceConnectionChange :: " + iceConnectionState.toString());
            }

            @Override
            public void onIceConnectionReceivingChange(boolean b) {
                //Logger.d(TAG, "onIceConnectionReceivingChange :: " + b);
            }

            @Override
            public void onIceGatheringChange(PeerConnection.IceGatheringState iceGatheringState) {
                //Logger.d(TAG, "onIceGatheringChange :: " + iceGatheringState.toString());
            }

            @Override
            public void onIceCandidatesRemoved(IceCandidate[] iceCandidates) {
                //Logger.d(TAG, "onIceCandidatesRemoved :: " + iceCandidates.toString());
            }

            @Override
            public void onRemoveStream(MediaStream mediaStream) {
                //Logger.d(TAG, "onRemoveStream :: " + mediaStream.toString());
            }

            @Override
            public void onDataChannel(DataChannel dataChannel) {
                //Logger.d(TAG, "onDataChannel :: " + dataChannel.toString());
            }

            @Override
            public void onRenegotiationNeeded() {
                //Logger.d(TAG, "onRenegotiationNeeded");
            }

            @Override
            public void onAddTrack(RtpReceiver rtpReceiver, MediaStream[] mediaStreams) {
                //Logger.d(TAG, "onAddTrack :: " + mediaStreams.toString());
            }
        });
    }

    public void initCall() {
        webrtcTrackInitialize();
        countDownTimer = System.currentTimeMillis();

        if (isIncomingView && isAnswerBtnClick) {
            binding.acceptArea.performClick();
        } else {
            if (!isIncomingView) {
                doCall();
            }
        }
    }

    private void webrtcTrackInitialize() {
        //webrtc
        if (dataProvider.isVideoCall) {
            //create video track form camera and show it
            videoCaptureAndroid = createVideoCapture();
            if (videoCaptureAndroid == null) {
                binding.declineArea.performClick();
                return;
            }

            videoSource = mFactory.createVideoSource(false);
            SurfaceTextureHelper surfaceTextureHelper = SurfaceTextureHelper.create(Thread.currentThread().getName(), rootEglBase.getEglBaseContext());
            videoCaptureAndroid.initialize(surfaceTextureHelper, binding.sfLocalView.getContext(), videoSource.getCapturerObserver());
            videoCaptureAndroid.startCapture(1240, 720, 30);
            localVideoTrack = mFactory.createVideoTrack("100", videoSource);
            localVideoTrack.addSink(binding.sfLocalView);
        }

        //create audio track
        localAudioTrack = mFactory.createAudioTrack("101", mFactory.createAudioSource(new MediaConstraints()));
        if (peerConnection != null) {
            peerConnection.setAudioRecording(true);
            peerConnection.setAudioPlayout(true);
        }

        MediaStream mediaStream = mFactory.createLocalMediaStream("102");
        if (localAudioTrack != null) {
            mediaStream.addTrack(localAudioTrack);
        }
        if (localVideoTrack != null) {
            mediaStream.addTrack(localVideoTrack);
        }
        if (peerConnection != null) {
            peerConnection.addStream(mediaStream);
        }
    }

    // -=-=-=-=-
    private CameraVideoCapturer createVideoCapture() {
        CameraEnumerator enumerator;
        if (Camera2Enumerator.isSupported(act)) {
            enumerator = new Camera2Enumerator(act);
        } else {
            enumerator = new Camera1Enumerator(true);
        }
        for (String device : enumerator.getDeviceNames()) {
            if (enumerator.isFrontFacing(device)) {
                CameraVideoCapturer vCapture = enumerator.createCapturer(device, null);
                if (vCapture != null)
                    return vCapture;
            }
        }
        for (String device : enumerator.getDeviceNames()) {
            if (!enumerator.isFrontFacing(device)) {
                CameraVideoCapturer vCapture = enumerator.createCapturer(device, null);
                if (vCapture != null)
                    return vCapture;
            }
        }
        return null;
    }

    private List<PeerConnection.IceServer> setIceSeverData(List<PeerConnection.IceServer> peerIceServers) {
        JSONArray webrtcArray = act.generalFunc.getJsonArray("WEBRTC_ICE_SERVER_LIST", act.generalFunc.getJsonObject(act.generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        if (webrtcArray != null) {
            if (webrtcArray.length() > 1) {
                JSONObject mBannerObject = act.generalFunc.getJsonObject(webrtcArray, 1);

                String turn = act.generalFunc.getJsonValueStr("TURN_URL", mBannerObject);
                String uName = act.generalFunc.getJsonValueStr("USER_NAME", mBannerObject);
                String pass = act.generalFunc.getJsonValueStr("Password", mBannerObject);

                createIceObj(webrtcArray, "stun:openrelay.metered.ca:80", "turn:openrelay.metered.ca:80", "openrelayproject", "openrelayproject");
                createIceObj(webrtcArray, "stun:openrelay.metered.ca:80", "turn:openrelay.metered.ca:443", "openrelayproject", "openrelayproject");
                createIceObj(webrtcArray, "stun:stun.l.google.com:19302", turn, uName, pass);
                createIceObj(webrtcArray, "stun:stun1.l.google.com:19302", turn, uName, pass);
                createIceObj(webrtcArray, "stun:stun2.l.google.com:19302", turn, uName, pass);
                createIceObj(webrtcArray, "stun:stun3.l.google.com:19302", turn, uName, pass);
                createIceObj(webrtcArray, "stun:stun4.l.google.com:19302", turn, uName, pass);
            }

            for (int i = 0; i < webrtcArray.length(); i++) {
                JSONObject serverObj = act.generalFunc.getJsonObject(webrtcArray, i);

                String turn = act.generalFunc.getJsonValueStr("TURN_URL", serverObj);
                if (Utils.checkText(turn)) {
                    PeerConnection.IceServer peerIceServer = PeerConnection.IceServer.builder(turn).setUsername(act.generalFunc.getJsonValueStr("USER_NAME", serverObj)).setPassword(act.generalFunc.getJsonValueStr("Password", serverObj)).createIceServer();
                    peerIceServers.add(peerIceServer);
                }

                String stun = act.generalFunc.getJsonValueStr("STUN_URL", serverObj);
                if (Utils.checkText(stun)) {
                    PeerConnection.IceServer peerIceServer1 = PeerConnection.IceServer.builder(stun).createIceServer();
                    peerIceServers.add(peerIceServer1);
                }
            }
        }
        return peerIceServers;
    }

    private void createIceObj(JSONArray jsonArray, String stun, String turn, String uName, String pass) {
        try {
            JSONObject Obj1 = new JSONObject();
            Obj1.put("STUN_URL", stun);
            Obj1.put("TURN_URL", turn);
            Obj1.put("USER_NAME", uName);
            Obj1.put("Password", pass);
            jsonArray.put(Obj1);
        } catch (JSONException e) {
            throw new RuntimeException(e);
        }
    }

    private static class SimpleSdpObserver implements SdpObserver {

        @Override
        public void onCreateSuccess(SessionDescription sessionDescription) {
        }

        @Override
        public void onSetSuccess() {
        }

        @Override
        public void onCreateFailure(String s) {
        }

        @Override
        public void onSetFailure(String s) {
        }
    }

    private void stopRinging() {
        if (mAudioPlayer != null) {
            if (isIncomingView) {
                mAudioPlayer.stopRingtone();
            } else {
                mAudioPlayer.stopProgressTone();
            }
        }
    }

    public void onDestroy() {
        stopRinging();
        mTimer.cancel();

        //
        if (peerConnection != null) {
            peerConnection.close();
            peerConnection = null;
        }
        if (audioManager != null) {
            audioManager.close();
            audioManager = null;
        }

        //--- Remove Track ----

        if (videoCaptureAndroid != null) {
            videoCaptureAndroid.dispose();
        }
        videoCaptureAndroid = null;

        //
        binding.sfLocalView.release();
        binding.sfRemoteView.release();

        // Audio
        if (audioSource != null) {
            audioSource.dispose();
        }
        audioSource = null;
        if (localAudioTrack != null) {
            localAudioTrack.dispose();
        }
        localAudioTrack = null;

        // Video
        if (videoSource != null) {
            videoSource.dispose();
        }
        videoSource = null;
        if (localVideoTrack != null) {
            localVideoTrack.dispose();
        }
        localVideoTrack = null;
    }

    //*****
    public void inComingAnswer(@NonNull String rtcData) {
        if (peerConnection != null) {
            peerConnection.setRemoteDescription(new SimpleSdpObserver(), new SessionDescription(SessionDescription.Type.fromCanonicalForm(
                    act.generalFunc.getJsonValue("type", rtcData).toLowerCase()),
                    act.generalFunc.getJsonValue("description", rtcData)));
            if (!iceCandidateList.isEmpty()) {
                for (int i = 0; i < iceCandidateList.size(); i++) {
                    sendRTCData(dataProvider, iceCandidateList.get(i), 0);
                }
            }
        }
    }

    public void addIceCandidate(@NonNull String rtcData) {
        if (peerConnection != null) {
            peerConnection.addIceCandidate(new IceCandidate(act.generalFunc.getJsonValue("sdpMid", rtcData), Integer.parseInt(act.generalFunc.getJsonValue("sdpMLineIndex", rtcData)), act.generalFunc.getJsonValue("sdp", rtcData)));
        }
    }

    public void cameraSwitched(@NonNull String rtcData) {
        binding.sfRemoteView.setMirror(act.generalFunc.getJsonValue("isFrontCamera", rtcData).equalsIgnoreCase("Yes"));
    }

    public void onAnotherCall(@NonNull JSONObject memberDataObj) {
        if (MyApp.getInstance().lastAct != null) {
            act.generalFunc.showMessage(act.generalFunc.getCurrentView(MyApp.getInstance().lastAct),
                    act.generalFunc.getJsonValueStr("iFromMemberName", memberDataObj) + " " + act.generalFunc.retrieveLangLBl("", "LBL_ON_ANOTHER_CALL_TXT"));
        }
        act.finish();
    }

    public void busyCall(@NonNull JSONObject memberDataObj) {
        if (isCallStart && !act.generalFunc.getJsonValueStr("iFromMemberId", memberDataObj).equalsIgnoreCase(dataProvider.toMemberId)) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setFromMemberId(act.generalFunc.getJsonValueStr("iToMemberId", memberDataObj))
                    .setFromMemberType(act.generalFunc.getJsonValueStr("iToMemberType", memberDataObj))
                    .setFromMemberName(act.generalFunc.getJsonValueStr("iToMemberName", memberDataObj))
                    .setFromMemberImage(act.generalFunc.getJsonValueStr("iToMemberImage", memberDataObj))

                    .setToMemberId(act.generalFunc.getJsonValueStr("iFromMemberId", memberDataObj))
                    .setToMemberType(act.generalFunc.getJsonValueStr("iFromMemberType", memberDataObj))
                    .setToMemberName(act.generalFunc.getJsonValueStr("iFromMemberName", memberDataObj))
                    .setToMemberImage(act.generalFunc.getJsonValueStr("iFromMemberImage", memberDataObj))

                    .setVideoCall(act.generalFunc.getJsonValueStr("isVideoCall", memberDataObj).equalsIgnoreCase("Yes"))
                    .build();
            sendRTCData(mDataProvider, "ON_ANOTHER_CALL", 0);
        }
    }

    private void onCallEstablished() {
        stopRinging();
        countDownTimer = System.currentTimeMillis();
        isCallStart = true;
        act.intiCallingView();

        //
        RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) binding.sfLocalView.getLayoutParams();
        params.addRule(RelativeLayout.ALIGN_PARENT_RIGHT);
        params.height = act.getResources().getDimensionPixelSize(R.dimen._110sdp);
        params.width = act.getResources().getDimensionPixelSize(R.dimen._90sdp);
        params.topMargin = act.getResources().getDimensionPixelSize(R.dimen._75sdp);
        params.rightMargin = act.getResources().getDimensionPixelSize(R.dimen._15sdp);
        binding.sfLocalView.setLayoutParams(params);

        if (MyApp.getInstance().activeTripAct != null) {
            MyApp.getInstance().activeTripAct.isVideoCallGenerate();
        }
    }

    // button Click events
    public void speakerEvent(boolean isSpeaker) {
        if (isSpeaker) {
            if (mMediaStream != null && mMediaStream.audioTracks != null) {
                mMediaStream.audioTracks.get(0).setEnabled(true);
            }
        }
        audioManager.setSpeakerphoneOn(isSpeaker);
    }

    public void muteEvent(boolean isMute) {
        if (localAudioTrack != null) {
            localAudioTrack.setEnabled(isMute);
        }
    }

    public void cameraEvent(boolean isCamera) {
        if (localVideoTrack != null) {
            localVideoTrack.setEnabled(isCamera);
        }
    }

    public void switchCameraEvent() {
        if (videoCaptureAndroid != null) {
            videoCaptureAndroid.switchCamera(new CameraVideoCapturer.CameraSwitchHandler() {
                @Override
                public void onCameraSwitchDone(boolean b) {
                    act.onCameraSwitchDone(b);
                    try {
                        JSONObject object = new JSONObject();
                        object.put("type", "cameraSwitched");
                        object.put("isFrontCamera", b ? "Yes" : "No");
                        sendRTCData(dataProvider, object.toString(), 0);
                    } catch (Exception e) {
                        Logger.e(TAG, "switchCamera | JSONException" + e.getMessage());
                    }
                }

                @Override
                public void onCameraSwitchError(String s) {

                }
            });
        }
    }

    ///============================
    private void doCall() {
        MediaConstraints mediaConstraints = new MediaConstraints();
        mediaConstraints.mandatory.add(new MediaConstraints.KeyValuePair("OfferToReceiveAudio", "true"));
        mediaConstraints.mandatory.add(new MediaConstraints.KeyValuePair("OfferToReceiveVideo", "true"));
        mediaConstraints.mandatory.add(new MediaConstraints.KeyValuePair("DtlsSrtpKeyAgreement", "true"));
        if (peerConnection != null) {
            peerConnection.createOffer(new SimpleSdpObserver() {
                @Override
                public void onCreateSuccess(SessionDescription sessionDescription) {
                    peerConnection.setLocalDescription(new SimpleSdpObserver(), sessionDescription);
                    try {
                        JSONObject obj = new JSONObject();
                        obj.put("type", sessionDescription.type.canonicalForm());
                        obj.put("description", sessionDescription.description);
                        sendRTCData(dataProvider, obj.toString(), 0);
                    } catch (JSONException e) {
                        Logger.e(TAG, "createOffer | JSONException" + e.getMessage());
                    }
                }
            }, mediaConstraints);
        }
    }

    public void doAnswer() {
        if (peerConnection != null) {
            peerConnection.setRemoteDescription(new SimpleSdpObserver(), new SessionDescription(OFFER, act.generalFunc.getJsonValue("description", act.generalFunc.retrieveValue("rtcOfferDescription"))));
            peerConnection.createAnswer(new SimpleSdpObserver() {
                @Override
                public void onCreateSuccess(SessionDescription sessionDescription) {
                    peerConnection.setLocalDescription(new SimpleSdpObserver(), sessionDescription);
                    try {
                        JSONObject obj = new JSONObject();
                        obj.put("type", sessionDescription.type.canonicalForm());
                        obj.put("description", sessionDescription.description);
                        sendRTCData(dataProvider, obj.toString(), 0);
                    } catch (JSONException e) {
                        Logger.e(TAG, "createAnswer | JSONException" + e.getMessage());
                    }
                }
            }, new MediaConstraints());
        }
    }

    private JSONObject getMemberData(@NonNull MediaDataProvider mediaDataProvider) {
        JSONObject obj = new JSONObject();
        try {
            obj.put("iFromMemberId", mediaDataProvider.fromMemberId);
            obj.put("iFromMemberType", mediaDataProvider.fromMemberType);
            obj.put("iFromMemberName", mediaDataProvider.fromMemberName);
            obj.put("iFromMemberImage", mediaDataProvider.fromMemberImage);

            obj.put("iToMemberId", mediaDataProvider.toMemberId);
            obj.put("iToMemberType", mediaDataProvider.toMemberType);
            obj.put("iToMemberName", mediaDataProvider.toMemberName);
            obj.put("iToMemberImage", mediaDataProvider.toMemberImage);
            obj.put("isVideoCall", mediaDataProvider.isVideoCall ? "Yes" : "No");
        } catch (JSONException e) {
            Logger.e(TAG, "getMemberData | JSONException" + e.getMessage());
        }
        return obj;
    }

    public void sendRTCData(@NonNull MediaDataProvider mediaDataProvider, @NonNull String dataStr, int repeatCount) {
        if (repeatCount > 3) {
            return;
        }

        JSONObject vMsgObj = new JSONObject();
        try {
            vMsgObj.put("MEMBER_DATA", getMemberData(mediaDataProvider));
            vMsgObj.put("RTC_DATA", dataStr);
        } catch (JSONException e) {
            Logger.e(TAG, "sendRTCData | JSONException" + e.getMessage());
        }

        String vData = vMsgObj.toString();
        Logger.e(TAG, "sendRTCData | JSON_DATA :: " + vData);

        AppService.getInstance().sendMessage(SocketEvents.VOIP_SERVICE, vData, 10 * 1000, (name, errorObj, dataObj) -> {
            if (errorObj != null) {
                if (!act.isFinishing()) {
                    int rCount = repeatCount + 1;
                    new Handler(Looper.getMainLooper()).postDelayed(() -> sendRTCData(mediaDataProvider, dataStr, rCount), 2000);
                    act.generalFunc.showMessage(binding.stateTxt, act.generalFunc.retrieveLangLBl("We're unable to communicate with the server. Please check your internet connection.", "LBL_TRY_AGAIN_LATER_TXT"));
                }
            }
        });
    }
}