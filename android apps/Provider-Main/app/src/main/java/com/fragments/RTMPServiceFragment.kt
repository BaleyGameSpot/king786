package com.fragments

import android.Manifest
import android.annotation.SuppressLint
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Rect
import android.hardware.camera2.CameraCharacteristics
import android.net.Uri
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.provider.Settings
import android.view.Gravity
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import androidx.activity.result.ActivityResultLauncher
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.content.ContextCompat
import androidx.databinding.DataBindingUtil
import com.act.WorkingtrekActivity
import com.activity.ParentActivity
import com.general.files.GeneralFunctions
import com.general.files.MyApp
import com.haishinkit.codec.VideoCodec
import com.haishinkit.event.Event
import com.haishinkit.event.EventUtils
import com.haishinkit.event.IEventListener
import com.haishinkit.media.AudioRecordSource
import com.haishinkit.media.Camera2Source
import com.haishinkit.rtmp.RtmpConnection
import com.haishinkit.rtmp.RtmpStream
import com.haishinkit.view.HkSurfaceView
import com.buddyverse.providers.R
import com.buddyverse.providers.databinding.FragmentRtmpBinding
import com.utils.Logger
import com.utils.Utils
import org.json.JSONObject
import java.util.Locale
import java.util.Timer
import java.util.TimerTask

class RTMPServiceFragment(private var isPlay: Boolean) : BaseFragment() {

    private val TAG = RTMPServiceFragment::class.java.simpleName
    private lateinit var binding: FragmentRtmpBinding

    private lateinit var act: ParentActivity
    private var mDetails: JSONObject? = null
    fun newInstance(pAct: ParentActivity) {
        this.act = pAct
        this.mDetails = act.generalFunc.getJsonObject("RTMP_MEDIA_DETAILS", act.obj_userProfile)
    }

    private lateinit var connection: RtmpConnection
    private lateinit var stream: RtmpStream
    private lateinit var cameraSource: Camera2Source

    private val myPermissions = listOf(Manifest.permission.CAMERA, Manifest.permission.RECORD_AUDIO)
    private var isConnection: Boolean = false
    private var isStream: Boolean = false
    private var isFullView: Boolean = false
    private var ratio: Float = 0.0f

    private val mTimer = Timer()
    private var idleTimer: Long = 0
    private var idleSec: Long = 0

    @SuppressLint("SetTextI18n")
    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_rtmp, container, false)

        binding.leftView.visibility = View.GONE
        binding.leftView.setOnClickListener {
            binding.leftView.visibility = View.GONE
            binding.viewArea.visibility = View.VISIBLE
        }
        binding.rightView.visibility = View.GONE
        binding.rightView.setOnClickListener {
            binding.rightView.visibility = View.GONE
            binding.viewArea.visibility = View.VISIBLE
            (act as? WorkingtrekActivity)?.setVideoViewX()
        }
        if (act.generalFunc.getJsonValueStr("MEDIA_CONTROLS", mDetails).equals("Yes", ignoreCase = true)) {
            binding.playPause.visibility = View.VISIBLE
        } else {
            binding.playPause.visibility = View.GONE
        }
        if (isPlay) {
            binding.playPause.setImageResource(R.drawable.ic_pause)
        } else {
            binding.playPause.setImageResource(R.drawable.ic_play)
        }
        binding.playPause.setOnClickListener {
            if (isStream || !isPlay) {
                if (isPlay) {
                    (act as? WorkingtrekActivity)?.reStartRTMPFrg(false)
                } else {
                    isPlay = true
                    binding.playPause.setImageResource(R.drawable.ic_pause)
                    startStopLive()
                }
            } else {
                act.generalFunc.showGeneralMessage("", act.generalFunc.retrieveLangLBl("", "LBL_PLEASE_WAIT"));
            }
        }
        //
        binding.fullViewImg.setOnClickListener {
            val params = binding.viewArea.layoutParams as LinearLayout.LayoutParams
            if (isFullView) {
                params.width = act.getResources().getDimensionPixelSize(R.dimen._100sdp)
                params.height = (params.width * ratio).toInt()
                params.setMargins(0, 0, 0, 0)
                binding.fullViewImg.setImageResource(R.drawable.ic_view_expand)
                (act as? WorkingtrekActivity)?.setFullScreen(false)
                isFullView = false
            } else {
                val margin: Int = act.getResources().getDimensionPixelSize(R.dimen._10sdp)
                params.width = Utils.getScreenPixelWidth(act).toInt() - (margin * 2)
                params.height = Utils.getScreenPixelHeight(act).toInt() - (getBottomNavigationBarHeight(act) + (margin * 2))
                params.setMargins(margin, margin, margin, margin)
                params.gravity = Gravity.CENTER
                binding.fullViewImg.setImageResource(R.drawable.ic_view_collpand)
                (act as? WorkingtrekActivity)?.setFullScreen(true)
                isFullView = true
            }
            binding.viewArea.layoutParams = params
        }

        requestCameraAndMicrophonePermissionsLauncher.launch(myPermissions.toTypedArray())
        setTimer()
        //
        return binding.root
    }

    private fun setTimer() {
        mTimer.schedule(object : TimerTask() {
            override fun run() {
                act.runOnUiThread {
                    if (isStream) {
                        val totalSeconds: Long = (System.currentTimeMillis() - idleTimer) / 1000
                        val minutes = totalSeconds / 60
                        val seconds = totalSeconds % 60
                        binding.timeTxt.text = String.format(Locale.ENGLISH, "%02d:%02d", minutes, seconds)
                    }
                }
            }
        }, 1000, 1000)
    }

    private fun createStreamer() {
        Logger.d(TAG, "createStreamer")
        isConnection = true

        connection = RtmpConnection()
        stream = RtmpStream(act, connection)
        stream.addEventListener("", object : IEventListener {
            override fun handleEvent(event: Event) {
                Logger.d(TAG, "stream>> $event")
            }
        })
        stream.screen.frame = Rect(
            0, 0,
            (Utils.getScreenPixelWidth(act).toInt()),
            (Utils.getScreenPixelHeight(act).toInt())
        )

        //
        val videoWidth = act.generalFunc.getJsonValueStr("videoWidth", mDetails)
        val videoHeight = act.generalFunc.getJsonValueStr("videoHeight", mDetails)
        ratio = videoWidth.toFloat() / videoHeight.toFloat()

        val bitRate = act.generalFunc.getJsonValueStr("bitRate", mDetails)
        val maxFrame = act.generalFunc.getJsonValueStr("maxKeyFrameIntervalDuration", mDetails)

        stream.videoSetting.width =
            GeneralFunctions.parseIntegerValue(VideoCodec.DEFAULT_WIDTH, videoWidth)
        stream.videoSetting.height =
            GeneralFunctions.parseIntegerValue(VideoCodec.DEFAULT_HEIGHT, videoHeight)

        stream.videoSetting.bitRate =
            GeneralFunctions.parseIntegerValue(VideoCodec.DEFAULT_BIT_RATE, bitRate)
        stream.videoSetting.IFrameInterval =
            GeneralFunctions.parseIntegerValue(VideoCodec.DEFAULT_I_FRAME_INTERVAL, maxFrame)

        //
        if (act.generalFunc.getJsonValueStr("AUDIO_ENABLED", mDetails)
                .equals("Yes", ignoreCase = true)
        ) {
            stream.attachAudio(AudioRecordSource(act))
        }

        cameraSource = Camera2Source(act).apply {
            open(CameraCharacteristics.LENS_FACING_FRONT)
        }

        stream.attachVideo(cameraSource)
        connection.addEventListener(Event.RTMP_STATUS, object : IEventListener {
            override fun handleEvent(event: Event) {
                Logger.d(TAG, "connection >> $event")
                val data = EventUtils.toMap(event)
                val code = data["code"].toString()
                if (code == RtmpConnection.Code.CONNECT_SUCCESS.rawValue) {
                    isStream = true
                    idleTimer = System.currentTimeMillis()
                    idleSec = 0
                    stream.publish(act.generalFunc.getJsonValueStr("MEDIA_STREAM_URI_PUBLISH", mDetails))
                }
            }
        })

        //
        HkSurfaceView(act).attachStream(stream)
        binding.cameraView.attachStream(stream)
        val params = binding.viewArea.layoutParams as LinearLayout.LayoutParams
        params.height = (params.width * ratio).toInt()
        binding.viewArea.layoutParams = params

        startStopLive()
    }

    private fun startStopLive() {
        if (!connection.isConnected && isPlay) {
            connection.connect(act.generalFunc.getJsonValueStr("MEDIA_STREAM_URI_PLAY", mDetails))
            binding.playPause.setImageResource(R.drawable.ic_pause)
        }
    }

    fun internetConnection(isNetConnection: Boolean, reCount: Int) {
        if (isNetConnection) {
            if (act.intCheck.isNetworkConnected) {
                requestCameraAndMicrophonePermissionsLauncher.launch(myPermissions.toTypedArray())
            } else {
                if (reCount > 6) {
                    return
                }
                if (!isConnection) {
                    Handler(Looper.getMainLooper()).postDelayed({
                        internetConnection(true, reCount)
                    }, 2000)
                }
            }
        } else {
            if (isConnection) {
                isConnection = false
                connection.dispose()
                stream.close()
                stream.screen.dispose()
                cameraSource.close()
                cameraSource.screen.dispose()
            }
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        Logger.d(TAG, "onDestroy")
        mTimer.cancel()
        if (isConnection) {
            connection.dispose()
            stream.close()
            stream.screen.dispose()
            cameraSource.close()
            cameraSource.screen.dispose()
            //
            if (connection.isConnected) {
                try {
                    connection.close()
                } catch (e: Exception) {
                    Logger.e(TAG, "stopLive | Exception >> $e")
                }
            }
        }
    }

    //------------------------------------------------------------------------------------------
    private val requestCameraAndMicrophonePermissionsLauncher =
        registerForActivityResult(ActivityResultContracts.RequestMultiplePermissions()) { permissions ->
            if (permissions.toList().all {
                    it.second
                }) {
                when {
                    hasPermissions(requireActivity(), *myPermissions.toTypedArray()) -> {
                        createStreamer()
                    }
                }
            } else {
                showPermissionError()
            }
        }

    private val settingLauncher: ActivityResultLauncher<Intent> =
        registerForActivityResult(ActivityResultContracts.StartActivityForResult()) {
            when {
                hasPermissions(requireActivity(), *myPermissions.toTypedArray()) -> {
                    createStreamer()
                }

                else -> {
                    showPermissionError()
                }
            }
        }

    private fun hasPermissions(context: Context, vararg permissions: String): Boolean =
        permissions.all {
            ContextCompat.checkSelfPermission(context, it) == PackageManager.PERMISSION_GRANTED
        }

    private fun showPermissionError() {
        act.generalFunc.showGeneralMessage(
            "",
            act.generalFunc.retrieveLangLBl(
                "Application requires some permission to be granted to work. Please allow it.",
                "LBL_ALLOW_PERMISSIONS_APP"
            ), "", act.generalFunc.retrieveLangLBl("Allow All", "LBL_SETTINGS")
        ) { buttonId: Int ->
            if (buttonId == 0) {
                act.finish()
            } else {
                val intent = Intent()
                intent.setAction(Settings.ACTION_APPLICATION_DETAILS_SETTINGS)
                val uri = Uri.fromParts(
                    "package",
                    MyApp.getInstance().applicationContext.packageName,
                    null
                )
                intent.setData(uri)
                settingLauncher.launch(intent)
            }
        }
    }

    fun handleMinimize(isSmallViewVisible: Boolean, isLeft: Boolean) {
        if (isSmallViewVisible) {
            if (isLeft) {
                binding.leftView.visibility = View.VISIBLE
                binding.viewArea.visibility = View.GONE
            } else {
                binding.rightView.visibility = View.VISIBLE
                binding.viewArea.visibility = View.GONE
            }
        }
    }

    @SuppressLint("InternalInsetResource")
    fun getBottomNavigationBarHeight(context: Context): Int {
        val resourceId = context.resources.getIdentifier("navigation_bar_height", "dimen", "android")
        return if (resourceId > 0) {
            context.resources.getDimensionPixelSize(resourceId) + act.getResources().getDimensionPixelSize(R.dimen._10sdp)
        } else {
            0
        }
    }
}