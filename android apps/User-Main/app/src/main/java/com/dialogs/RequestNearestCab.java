package com.dialogs;

import static io.realm.Realm.getApplicationContext;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.graphics.drawable.ColorDrawable;
import android.view.View;
import android.view.Window;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.core.content.ContextCompat;

import com.act.CarWashBookingDetailsActivity;
import com.act.MainActivity;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.PaintView;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

/**
 * Created by Admin on 11-07-2016.
 */
public class RequestNearestCab implements Runnable, GenerateAlertBox.HandleAlertBtnClick {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    public Dialog dialogRequestNearestCab;
    private GenerateAlertBox generateAlert;
    private String driverIds, cabRequestedJson;
    private boolean isCancelBtnClick = false;

    public RequestNearestCab(Context mContext, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
    }

    public void setRequestData(String driverIds, String cabRequestedJson) {
        this.driverIds = driverIds;
        this.cabRequestedJson = cabRequestedJson;
    }

    @SuppressLint("ResourceAsColor")
    @Override
    public void run() {
        dialogRequestNearestCab = new Dialog(mContext, R.style.NoActionBar);
        dialogRequestNearestCab.requestWindowFeature(Window.FEATURE_NO_TITLE);
        dialogRequestNearestCab.getWindow().setBackgroundDrawable(new ColorDrawable(android.graphics.Color.TRANSPARENT));
//        dialogRequestNearestCab.getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);
        dialogRequestNearestCab.getWindow().setStatusBarColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1));
        dialogRequestNearestCab.setContentView(R.layout.design_request_nearest_cab_dialog);

        MButton btn_type2 = ((MaterialRippleLayout) dialogRequestNearestCab.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));

        MTextView headerText = dialogRequestNearestCab.findViewById(R.id.headerText);
        MButton retryText = ((MaterialRippleLayout) dialogRequestNearestCab.findViewById(R.id.retryText)).getChildView();
        MButton cancelText = ((MaterialRippleLayout) dialogRequestNearestCab.findViewById(R.id.cancelText)).getChildView();
        MButton reqcancelText = ((MaterialRippleLayout) dialogRequestNearestCab.findViewById(R.id.reqcancelText)).getChildView();
        ImageView rotation_circle = dialogRequestNearestCab.findViewById(R.id.rotation_circle);
        RelativeLayout ll_radar_draw = dialogRequestNearestCab.findViewById(R.id.ll_radar_draw);
        final RippleBackground23 rippleBackground = dialogRequestNearestCab.findViewById(R.id.content);
        rippleBackground.startRippleAnimation();
        ll_radar_draw.addView(new PaintView(mContext));
        cancelText.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));
        reqcancelText.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));
        retryText.setText(generalFunc.retrieveLangLBl("Retry", "LBL_RETRY_TXT"));
        headerText.setText(generalFunc.retrieveLangLBl("", "LBL_REQUESTING_TXT"));

        MTextView noDriverText = dialogRequestNearestCab.findViewById(R.id.noDriverText);
        MTextView reqNoteText = dialogRequestNearestCab.findViewById(R.id.reqNoteText);

        showMsg(reqNoteText, noDriverText, false);

        Animation rotate = AnimationUtils.loadAnimation(getApplicationContext(), R.anim.rotate_clockwise);
        rotate.setRepeatCount(1);
        rotation_circle.startAnimation(rotate);


        retryText.setOnClickListener(v -> {
            if (mContext instanceof MainActivity) {
                ((MainActivity) mContext).retryReqBtnPressed(driverIds, cabRequestedJson);
            } else if (mContext instanceof CarWashBookingDetailsActivity) {
                ((CarWashBookingDetailsActivity) mContext).retryReqBtnPressed(driverIds, cabRequestedJson, true);
            }
        });
        cancelText.setOnClickListener(v -> {
            isCancelBtnClick = true;
            cancelRequestConfirm();
        });
        reqcancelText.setOnClickListener(v -> {
            isCancelBtnClick = true;
            cancelRequestConfirm();
        });

        ((ProgressBar) dialogRequestNearestCab.findViewById(R.id.mProgressBar)).setIndeterminate(true);
        dialogRequestNearestCab.setCancelable(false);
        dialogRequestNearestCab.setCanceledOnTouchOutside(false);
        LayoutDirection.setLayoutDirection(dialogRequestNearestCab);
        try {
            dialogRequestNearestCab.show();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
        (dialogRequestNearestCab.findViewById(R.id.cancelImgView)).setOnClickListener(view -> {
            if (!isCancelBtnClick) {
                isCancelBtnClick = true;
                cancelRequestConfirm();
            }
        });

        ((ProgressBar) dialogRequestNearestCab.findViewById(R.id.mProgressBar)).getIndeterminateDrawable().setColorFilter(
                mContext.getResources().getColor(R.color.appThemeColor_2), android.graphics.PorterDuff.Mode.SRC_IN);

        btn_type2.setOnClickListener(view -> {
            if (mContext instanceof MainActivity) {
                ((MainActivity) mContext).retryReqBtnPressed(driverIds, cabRequestedJson);
            } else if (mContext instanceof CarWashBookingDetailsActivity) {
                ((CarWashBookingDetailsActivity) mContext).retryReqBtnPressed(driverIds, cabRequestedJson, false);
            }
        });
    }

    private void showMsg(MTextView reqNoteText, MTextView noDriverText, boolean isDeclineFromDriver) {
        String retryMsg = generalFunc.retrieveLangLBl("Driver is not able to take your request. Please cancel request and try again OR retry.", "LBL_NOTE_NO_DRIVER_REQUEST");

        String headrMsg = "";

        ((MTextView) dialogRequestNearestCab.findViewById(R.id.noDriverNotifyTxt)).setText(retryMsg);
        if (mContext != null) {
            if (mContext instanceof MainActivity mainActivity) {
                if (mainActivity.getCurrentCabGeneralType().equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                    headrMsg = generalFunc.retrieveLangLBl("Finding A Provider", "LBL_FINDING_PROVIDER_HEADER_TXT");
                    retryMsg = generalFunc.retrieveLangLBl("Driver is not able to take your request. Please cancel request and try again OR retry.", "LBL_NOTE_NO_PROVIDER_REQUEST");
                    ((MTextView) dialogRequestNearestCab.findViewById(R.id.noDriverNotifyTxt)).setText(retryMsg);
                } else if (mainActivity.getCurrentCabGeneralType().equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
                    headrMsg = generalFunc.retrieveLangLBl("Finding A Driver", "LBL_FINDING_DRIVER_HEADER_TXT");
                    retryMsg = generalFunc.retrieveLangLBl("Driver is not able to take your request. Please cancel request and try again OR retry.", "LBL_NOTE_NO_DRIVER_REQUEST");
                    ((MTextView) dialogRequestNearestCab.findViewById(R.id.noDriverNotifyTxt)).setText(retryMsg);
                } else {
                    headrMsg = generalFunc.retrieveLangLBl("Finding A Carrier", "LBL_FINDING_CARRIER_HEADER_TXT");
                    retryMsg = generalFunc.retrieveLangLBl("Driver is not able to take your request. Please cancel request and try again OR retry.", "LBL_NOTE_NO_CARRIER_REQUEST");
                    ((MTextView) dialogRequestNearestCab.findViewById(R.id.noDriverNotifyTxt)).setText(retryMsg);
                }
            } else if (mContext instanceof CarWashBookingDetailsActivity) {
                headrMsg = generalFunc.retrieveLangLBl("Finding A Provider", "LBL_FINDING_PROVIDER_HEADER_TXT");
                retryMsg = generalFunc.retrieveLangLBl("Driver is not able to take your request. Please cancel request and try again OR retry.",
                        "LBL_NOTE_NO_PROVIDER_REQUEST");
                ((MTextView) dialogRequestNearestCab.findViewById(R.id.noDriverNotifyTxt)).setText(retryMsg);
                if (isDeclineFromDriver) {
                    retryMsg = generalFunc.retrieveLangLBl("", "LBL_PROVIDER_DECLINED_REQUEST_MSG");
                }
            }
        }
        reqNoteText.setText(headrMsg);
        noDriverText.setText(retryMsg);
    }

    public void setVisibleBottomArea(int visibility, boolean isDeclineFromDriver) {
        // Animation bottomUp = AnimationUtils.loadAnimation(mContext, R.anim.bottom_up);
        LinearLayout layout = dialogRequestNearestCab.findViewById(R.id.retryArea);

        MTextView noDriverText = dialogRequestNearestCab.findViewById(R.id.noDriverText);
        MTextView reqNoteText = dialogRequestNearestCab.findViewById(R.id.reqNoteText);
        showMsg(reqNoteText, noDriverText, isDeclineFromDriver);

        layout.setVisibility(visibility);

        if (layout.getVisibility() == View.VISIBLE) {
            LinearLayout reqLayout = dialogRequestNearestCab.findViewById(R.id.reqNoteArea);
            reqLayout.setVisibility(View.GONE);
        } else {
            LinearLayout reqLayout = dialogRequestNearestCab.findViewById(R.id.reqNoteArea);
            reqLayout.setVisibility(View.VISIBLE);
        }
    }

    public void setInVisibleBottomArea(int visibility) {
        //  Animation bottomUp = AnimationUtils.loadAnimation(mContext, R.anim.bottom_up);
        LinearLayout layout = dialogRequestNearestCab.findViewById(R.id.retryArea);
        layout.setVisibility(visibility);

        if (layout.getVisibility() == View.VISIBLE) {
            LinearLayout reqLayout = dialogRequestNearestCab.findViewById(R.id.reqNoteArea);
            reqLayout.setVisibility(View.GONE);
        } else {
            LinearLayout reqLayout = dialogRequestNearestCab.findViewById(R.id.reqNoteArea);
            reqLayout.setVisibility(View.VISIBLE);
        }
    }

    public void dismissDialog() {
        if (!((Activity) mContext).isFinishing() && dialogRequestNearestCab != null && dialogRequestNearestCab.isShowing()) {
            dialogRequestNearestCab.dismiss();
        }
    }

    private void releaseMainTask() {
        if (mContext != null && mContext instanceof MainActivity) {
            ((MainActivity) mContext).releaseScheduleNotificationTask();
        }
    }

    private void cancelRequestConfirm() {
        if (generateAlert != null) {
            generateAlert.closeAlertBox();
            generateAlert = null;
        }
        generateAlert = new GenerateAlertBox(mContext);
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(this);
        generateAlert.setContentMessage("",
                generalFunc.retrieveLangLBl("", "LBL_CONFIRM_REQUEST_CANCEL_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_TRIP_CANCEL_CONFIRM_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));
        generateAlert.showAlertBox();
    }

    @Override
    public void handleBtnClick(int btn_id) {
        if (btn_id == 0) {
            isCancelBtnClick = false;
            if (generateAlert != null) {
                generateAlert.closeAlertBox();
                generateAlert = null;
            }
        } else {
            if (generateAlert != null) {
                generateAlert.closeAlertBox();
                generateAlert = null;
            }
            cancelRequest();
        }
    }

    private void cancelRequest() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "cancelCabRequest");
        parameters.put("iUserId", generalFunc.getMemberId());

        ApiHandler.execute(mContext, parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    manageAfterCancelCabRequest("");
                } else {
                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                    if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY) || message.equals("LBL_SERVER_COMM_ERROR")) {
                        manageAfterCancelCabRequest(message);
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void releaseResources() {
        if (mContext != null && mContext instanceof MainActivity mainAct) {
            mainAct.mainHeaderFrag.releaseResources();
            mainAct.mainHeaderFrag = null;

            if (mainAct.cabSelectionFrag != null) {
                mainAct.stopOverPointsList.clear();
                mainAct.cabSelectionFrag.releaseResources();
                mainAct.cabSelectionFrag = null;
            }
        }
    }

    private void manageAfterCancelCabRequest(String message) {
        dismissDialog();
        releaseMainTask();

        if (Utils.checkText(message)) {
            releaseResources();
            generalFunc.restartApp();
        } else {
            if (mContext instanceof CarWashBookingDetailsActivity) {
                ((CarWashBookingDetailsActivity) mContext).requestNearestCab = null;
            } else if (mContext instanceof MainActivity) {
                releaseResources();
                MyApp.getInstance().restartWithGetDataApp();
            }
        }
    }
}