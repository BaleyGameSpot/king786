package com.fragments;

import android.annotation.SuppressLint;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Rect;
import android.media.MediaPlayer;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.text.Editable;
import android.text.TextWatcher;
import android.util.Base64;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;

import androidx.annotation.NonNull;
import androidx.databinding.DataBindingUtil;
import androidx.exifinterface.media.ExifInterface;

import com.act.ChatActivity;
import com.activity.ParentActivity;
import com.adapter.files.ChatMessagesRecycleAdapter;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenProgressUpdateDialog;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentChatDataBinding;
import com.model.SocketEvents;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class ChatDataFragment extends BaseFragment implements TextWatcher, ViewTreeObserver.OnGlobalLayoutListener, ChatMessagesRecycleAdapter.OnItemClickListener {

    private FragmentChatDataBinding binder;

    private View view;
    private GeneralFunctions generalFunc;

    private ChatActivity chatAct;

    private ChatMessagesRecycleAdapter chatAdapter;
    private final ArrayList<HashMap<String, String>> list_msgs = new ArrayList<>();

    private JSONObject obj_data;
    public HashMap<String, String> dataMap;
    private String vBookingNo = "", eServiceType = "";
    boolean isChatHistoryLoaded = false;
    private int uploadImgPos = -1;
    private OpenProgressUpdateDialog openProgressDialog;


    private Uri mFileSelectedUri;
    private String mFileSelectedPath;
    private FileSelector.FileType mFileSelectedType;

    public ChatDataFragment() {
    }

    public ChatDataFragment(@NonNull HashMap<String, String> dataMap) {
        this.dataMap = dataMap;
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        binder = DataBindingUtil.inflate(inflater, R.layout.fragment_chat_data, container, false);

        if (chatAct == null) {
            chatAct = (ChatActivity) getActivity();
            generalFunc = ((ParentActivity) MyApp.getInstance().getCurrentAct()).generalFunc;
        }

        if (dataMap == null && chatAct.dataMap != null) {
            dataMap = new HashMap<>();
            dataMap.putAll(chatAct.dataMap);
        }

        binder.chatParentLayout.getViewTreeObserver().addOnGlobalLayoutListener(this);

        view = binder.getRoot();

        initView();

        return view;
    }

    @SuppressLint("SetTextI18n")
    public void initView() {
        chatAct.currentVisibleFrag = this;

        if (dataMap == null) {
            generalFunc.showError(true);
            return;
        }

        if (generalFunc.isRTLmode()) {
            binder.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binder.toolbarInclude.backImgView);
        addToClickHandler(binder.msgBtn);
        binder.msgBtn.setImageResource(R.drawable.ic_chat_send_disable);

        addToClickHandler(binder.mediaImg);
        if (generalFunc.getJsonValueStr("ENABLE_PHOTO_UPLOAD_SERVICE_CHAT", chatAct.obj_userProfile).equalsIgnoreCase("Yes")) {
            binder.mediaImg.setVisibility(View.VISIBLE);
        } else {
            binder.mediaImg.setVisibility(View.GONE);
        }

        binder.input.setHint(generalFunc.retrieveLangLBl("Enter a message", "LBL_ENTER_MESSAGE"));
        binder.input.addTextChangedListener(this);
        chatAdapter = new ChatMessagesRecycleAdapter(chatAct, list_msgs, list_msgs.isEmpty(), this);
        binder.chatCategoryRecyclerView.setAdapter(chatAdapter);

        addToClickHandler(binder.closeImg);
        binder.closeImg.performClick();

        if (Utils.checkText(dataMap.get("vBookingNo"))) {
            binder.toolbarInclude.titleTxt.setText("#" + generalFunc.convertNumberWithRTL(dataMap.get("vBookingNo")));
        }

        getChatHistory();
    }

    @SuppressLint({"NotifyDataSetChanged", "SetTextI18n"})
    private void getChatHistory() {

        binder.mainArea.setVisibility(View.GONE);
        binder.progressBar.setVisibility(View.VISIBLE);

        if (!AppService.getInstance().getAppClient().isconnected()) {
            new Handler().postDelayed(ChatDataFragment.this::getChatHistory, 2500);
            return;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getMessageHistory");
        parameters.put("iFromMemberType", Utils.userType);
        parameters.put("iToMemberType", dataMap.get("iToMemberType"));
        parameters.put("iToMemberId", dataMap.get("iToMemberId"));

        parameters.put("iBiddingPostId", dataMap.get("iBiddingPostId"));
        parameters.put("iTripId", dataMap.get("iTripId"));
        parameters.put("iOrderId", dataMap.get("iOrderId"));

        ApiHandler.execute(chatAct, parameters, responseString -> {
            if (!Utils.checkText(responseString)) {
                generalFunc.showError(i -> binder.toolbarInclude.backImgView.performClick());
                return;
            }

            JSONObject objData = generalFunc.getJsonObject(responseString);

            if (GeneralFunctions.checkDataAvail(Utils.action_str, objData)) {
                JSONArray msgsArr = generalFunc.getJsonArray("data", objData);

                if (msgsArr != null) {
                    for (int i = 0; i < msgsArr.length(); i++) {
                        JSONObject obj_data = generalFunc.getJsonObject(msgsArr, i);

                        HashMap<String, String> msgDataMap = new Gson().fromJson(obj_data.toString(), new TypeToken<HashMap<String, String>>() {
                        }.getType());

                        if (i == (msgsArr.length() - 1)) {
                            if (dataMap.get("isOpenMediaDialog") != null && Objects.requireNonNull(dataMap.get("isOpenMediaDialog")).equalsIgnoreCase("Yes")) {
                                msgDataMap.put("isOpenMediaDialog", "Yes");
                            }
                        }

                        list_msgs.add(msgDataMap);

                    }

                    chatAdapter.notifyDataSetChanged();

                    binder.chatCategoryRecyclerView.scrollToPosition(list_msgs.size() - 1);
                }


                JSONObject SERVICE_DATA_OBJ = generalFunc.getJsonObject("SERVICE_DATA", objData);

                this.obj_data = SERVICE_DATA_OBJ;

                JSONObject memberData = generalFunc.getJsonObject("MemberData", SERVICE_DATA_OBJ);
                JSONObject serviceData = generalFunc.getJsonObject("ServiceData", SERVICE_DATA_OBJ);

                String vBookingNoTMP = generalFunc.getJsonValueStr("vBookingNo", serviceData);
                String vRideNoTMP = generalFunc.getJsonValueStr("vRideNo", serviceData);

                vBookingNo = vBookingNoTMP.trim().equalsIgnoreCase("") ? vRideNoTMP : vBookingNoTMP;
                eServiceType = generalFunc.getJsonValueStr("eType", serviceData);

                binder.userNameTxt.setText(generalFunc.getJsonValueStr("vName", memberData));
                if (Utils.checkText(generalFunc.getJsonValueStr("vServiceName", serviceData))) {
                    binder.catTypeText.setText(generalFunc.getJsonValueStr("vServiceName", serviceData));
                } else {
                    binder.catTypeText.setVisibility(View.GONE);
                    binder.shadowHeaderView.setVisibility(View.GONE);
                }

                String memberImg = generalFunc.getJsonValueStr("vImage", memberData);
                if (!Utils.checkText(memberImg)) {
                    memberImg = "Temp";
                }

                new LoadImage.builder(LoadImage.bind(memberImg), binder.userImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
                if (generalFunc.getJsonValueStr("vAvgRating", memberData).equalsIgnoreCase("")) {
                    binder.ratingArea.setVisibility(View.GONE);
                }
                binder.driverRating.setText(generalFunc.getJsonValueStr("vAvgRating", memberData));
                binder.toolbarInclude.titleTxt.setText("#" + generalFunc.convertNumberWithRTL(vBookingNo));

                binder.toolbarInclude.chatsubtitleTxt.setVisibility(View.VISIBLE);
                binder.toolbarInclude.chatsubtitleTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("tTripRequestDate", serviceData)));

                isChatHistoryLoaded = true;

                unHideMainView();
                if (mFileSelectedUri != null && mFileSelectedType != null) {
                    new Handler().postDelayed(() -> onFileSelected(mFileSelectedUri, mFileSelectedPath, mFileSelectedType), 500);
                }

                if (dataMap.get("isForPickupPhotoRequest") != null && Objects.requireNonNull(dataMap.get("isForPickupPhotoRequest")).equalsIgnoreCase("Yes")) {
                    sendMsg("", 0, true, false, 0);
                }

            } else {
                generalFunc.showError(i -> binder.toolbarInclude.backImgView.performClick());
            }
        });
    }

    private void unHideMainView() {
        if (obj_data != null && isChatHistoryLoaded) {
            binder.progressBar.setVisibility(View.GONE);
            binder.mainArea.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void beforeTextChanged(CharSequence charSequence, int i, int i1, int i2) {

    }

    @Override
    public void onTextChanged(CharSequence charSequence, int i, int i1, int i2) {

    }

    @Override
    public void onResume() {
        super.onResume();
        chatAct.currentVisibleFrag = this;
    }


    @Override
    public void afterTextChanged(Editable editable) {
        if (editable.length() == 0) {
            binder.msgBtn.setImageResource(R.drawable.ic_chat_send_disable);
        } else {
            binder.msgBtn.setImageResource(R.drawable.ic_chat_send);
        }
    }

    @Override
    public void onGlobalLayout() {
        Rect r = new Rect();
        binder.chatParentLayout.getWindowVisibleDisplayFrame(r);
        int screenHeight = binder.chatParentLayout.getRootView().getHeight();
        int keypadHeight = screenHeight - r.bottom;

        if (keypadHeight > screenHeight * 0.15) {
            binder.detailArea.setVisibility(View.GONE);
            binder.catTypeText.setVisibility(View.GONE);
        } else {
            binder.detailArea.setVisibility(View.VISIBLE);
            binder.catTypeText.setVisibility(View.VISIBLE);
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void handleIncomingMessages(HashMap<String, String> dataMap) {
        if (!isChatHistoryLoaded) {
            return;
        }
        final ArrayList<HashMap<String, String>> tempNew = new ArrayList<>();
        if (!list_msgs.isEmpty()) {

            String[] expiredIdsStr = null;
            if (dataMap.containsKey("expired_ids")) {
                String exIds = dataMap.get("expired_ids");
                if (Utils.checkText(exIds)) {
                    expiredIdsStr = exIds.split(",");
                }
            }

            boolean isUpdateRow = false;
            for (int i = 0; i < list_msgs.size(); i++) {
                if (Objects.requireNonNull(list_msgs.get(i).get("_id")).equalsIgnoreCase(dataMap.get("_id"))) {
                    tempNew.add(dataMap);
                    isUpdateRow = true;
                } else {
                    if (expiredIdsStr != null) {
                        for (String exId : expiredIdsStr) {
                            if (exId.equalsIgnoreCase(list_msgs.get(i).get("_id"))) {
                                list_msgs.get(i).put("isExpired", "Yes");
                            }
                        }
                    }

                    tempNew.add(list_msgs.get(i));
                    if (!isUpdateRow && i == (list_msgs.size() - 1)) {
                        tempNew.add(dataMap);
                    }
                }
            }
        } else {
            tempNew.add(dataMap);
        }
        list_msgs.clear();
        list_msgs.addAll(tempNew);

        chatAdapter.notifyDataSetChanged();

        binder.chatCategoryRecyclerView.scrollToPosition(list_msgs.size() - 1);

        if (dataMap.get("isPlaySound") != null && Objects.requireNonNull(dataMap.get("isPlaySound")).equalsIgnoreCase("Yes")) {
            try {
                MediaPlayer.create(chatAct, R.raw.chat_msg_received).start();
            } catch (Exception ignored) {

            }
        }
    }

    @Override
    public void onImageViewClick(int selPos, HashMap<String, String> imgList) {
        binder.carouselViewArea.setVisibility(View.VISIBLE);
        binder.loading.setVisibility(View.VISIBLE);

        int v11sdp = getResources().getDimensionPixelSize(R.dimen._11sdp);
        int sWidth = (int) (Utils.getScreenPixelWidth(chatAct) - (v11sdp * 2));
        int maxHeight = (int) (Utils.getScreenPixelHeight(chatAct) - (v11sdp * 2));

        try {
            binder.fullImage.zoomImageView.setPadding(v11sdp,0,v11sdp,0);
            String imgUrl = Utils.getResizeImgURL(chatAct, Objects.requireNonNull(imgList.get("vFile")), sWidth, 0, maxHeight);
            new LoadImage.builder(LoadImage.bind(imgUrl), binder.fullImage.zoomImageView).setPicassoListener(new LoadImage.PicassoListener() {
                @Override
                public void onSuccess() {
                    binder.loading.setVisibility(View.GONE);
                }

                @Override
                public void onError() {
                    binder.loading.setVisibility(View.GONE);
                }
            }).build();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    public boolean getFullImageView() {
        if (binder.carouselViewArea.getVisibility() == View.VISIBLE) {
            binder.carouselViewArea.setVisibility(View.GONE);
            return true;
        }
        return false;
    }

    @Override
    public void onUploadClick(int position) {
        uploadImgPos = position;
        chatAct.storeDataToBundle("uploadImgPos", "" + uploadImgPos);
        if (dataMap != null) {
            for (String key : dataMap.keySet()) {
                chatAct.storeDataToBundle(key, dataMap.get(key));
            }
        }
        chatAct.getFileSelector().openFileSelection(FileSelector.FileType.Image);
    }


    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        if (list_msgs.isEmpty()) {
            mFileSelectedUri = mFileUri;
            mFileSelectedPath = mFilePath;
            mFileSelectedType = mFileType;
            uploadImgPos = Integer.parseInt(chatAct.retrieveDataFromBundle("uploadImgPos"));
            return;
        }

        mFileSelectedUri = null;
        mFileSelectedPath = "";
        mFileSelectedType = null;

        if (uploadImgPos == -1) {
            openProgressDialog = new OpenProgressUpdateDialog(chatAct, generalFunc, null, generalFunc.retrieveLangLBl("", "LBL_UPLOADING_TXT"));
            try {
                openProgressDialog.run();
                openProgressDialog.updateProgress(-1);
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        } else {
            chatAdapter.uploadView(true, false);
        }

        try {
            final InputStream imageStream = chatAct.getContentResolver().openInputStream(mFileUri);
            Bitmap selectedImage = BitmapFactory.decodeStream(imageStream);

            ByteArrayOutputStream baos = new ByteArrayOutputStream();
            selectedImage.compress(Bitmap.CompressFormat.JPEG, 70, baos);
            String encImage = Base64.encodeToString(baos.toByteArray(), Base64.DEFAULT);

            int vExifInfo = new ExifInterface(new File(mFilePath).getAbsoluteFile()).getAttributeInt(ExifInterface.TAG_ORIENTATION, ExifInterface.ORIENTATION_NORMAL);
            if (Utils.checkText(encImage)) {
                sendMsg(encImage, vExifInfo, uploadImgPos != -1, true, 0);
            } else {
                if (uploadImgPos == -1) {
                    if (openProgressDialog != null && openProgressDialog.dialog_img_update != null) {
                        openProgressDialog.dialog_img_update.cancel();
                    }
                    generalFunc.showMessage(binder.mediaImg, generalFunc.retrieveLangLBl("", "LBL_ERROR_OCCURED"));
                } else {
                    chatAdapter.uploadView(false, true);
                }
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
            if (uploadImgPos == -1) {
                if (openProgressDialog != null && openProgressDialog.dialog_img_update != null) {
                    openProgressDialog.dialog_img_update.cancel();
                }
                generalFunc.showMessage(binder.mediaImg, generalFunc.retrieveLangLBl("", "LBL_SOMETHING_WENT_WRONG_MSG"));
            } else {
                chatAdapter.uploadView(false, true);
            }
        }
    }

    @SuppressLint("SetTextI18n")
    public void onClickView(View view) {
        Utils.hideKeyboard(requireActivity());
        int i = view.getId();
        if (i == binder.toolbarInclude.backImgView.getId()) {
            requireActivity().getOnBackPressedDispatcher().onBackPressed();

        } else if (i == binder.closeImg.getId()) {
            binder.carouselViewArea.setVisibility(View.GONE);

        } else if (i == binder.mediaImg.getId()) {
            uploadImgPos = -1;
            chatAct.getFileSelector().openFileSelection(FileSelector.FileType.Image);

        } else if (i == binder.msgBtn.getId()) {
            if (Utils.checkText(binder.input) && !Utils.getText(binder.input).isEmpty()) {
                sendMsg(Objects.requireNonNull(binder.input.getText()).toString(), 0, false, false, 0);
            }
        }
    }

    private void sendMsg(@NonNull String tMessage, int vExifInfo, boolean imgRequest, boolean imgUpload, int repeatCount) {
        if (AppService.getInstance() == null || !AppService.getInstance().getAppClient().isconnected()) {
            return;
        }

        HashMap<String, String> dataMap = new HashMap<>();
        dataMap.put("iFromMemberId", generalFunc.getMemberId());
        dataMap.put("iFromMemberType", Utils.app_type);
        dataMap.put("iFromMemberImage", generalFunc.getJsonValueStr(Utils.app_type.equalsIgnoreCase("Passenger") ? "vImgName" : "vImage", chatAct.obj_userProfile));

        dataMap.put("iToMemberId", generalFunc.getJsonValueStr("iMemberId", generalFunc.getJsonObject("MemberData", obj_data)));
        dataMap.put("iToMemberType", generalFunc.getJsonValueStr("iMemberType", generalFunc.getJsonObject("MemberData", obj_data)));
        String uri = generalFunc.getJsonValueStr("vImage", generalFunc.getJsonObject("MemberData", obj_data));
        if (Utils.checkText(uri)) {
            String[] path = uri.split("/");
            dataMap.put("iToMemberImage", path[path.length - 1]);
        }

        dataMap.put("iTripId", ChatDataFragment.this.dataMap.get("iTripId"));
        dataMap.put("iBiddingPostId", ChatDataFragment.this.dataMap.get("iBiddingPostId"));
        dataMap.put("iOrderId", ChatDataFragment.this.dataMap.get("iOrderId"));

        dataMap.put("tMessage", tMessage);
        dataMap.put("vBookingNo", vBookingNo);
        dataMap.put("eServiceType", eServiceType);

        if (imgRequest) {
            dataMap.put("isForPickupPhotoRequest", "Yes");
            if (uploadImgPos != -1) {
                dataMap.put("_id", list_msgs.get(uploadImgPos).get("_id"));
            }
        }
        if (imgUpload || imgRequest) {
            dataMap.put("tMessage", "");
            dataMap.put("vFile", tMessage);

            dataMap.put("vExifInfo", "" + vExifInfo);
        }

        binder.msgBtn.setEnabled(false);
        binder.input.setEnabled(false);

        Logger.e("JSON_DATA", "::" + (new Gson()).toJson(dataMap));

        AppService.getInstance().sendMessage(SocketEvents.CHAT_SERVICE, (new Gson()).toJson(dataMap), imgUpload || imgRequest ? 120 * 1000 :
                10 * 1000, (name, errorObj, dataObj) -> {

            if (errorObj == null) {
                if (openProgressDialog != null && openProgressDialog.dialog_img_update != null) {
                    openProgressDialog.dialog_img_update.cancel();
                }
                if (imgRequest && uploadImgPos != -1) {
                    chatAdapter.uploadView(false, false);
                }

                try {
                    MediaPlayer.create(chatAct, R.raw.chat_msg_sent).start();
                } catch (Exception ignored) {

                }
                binder.input.setText("");

            } else {
                if (!chatAct.isFinishing()) {
                    generalFunc.showMessage(binder.msgBtn, generalFunc.retrieveLangLBl("We're unable to communicate with the server. Please check your internet connection.", "LBL_TRY_AGAIN_LATER_TXT"));
                    if (openProgressDialog != null && openProgressDialog.dialog_img_update != null) {
                        openProgressDialog.dialog_img_update.cancel();
                    }
                    if (imgRequest && uploadImgPos != -1) {
                        chatAdapter.uploadView(false, true);
                    }
                }
            }

            binder.input.setEnabled(true);
            binder.msgBtn.setEnabled(true);
        });
    }
}