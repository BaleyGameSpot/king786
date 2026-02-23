package com.dialogs;

import static io.realm.Realm.getApplicationContext;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.graphics.drawable.ColorDrawable;
import android.os.Handler;
import android.os.Looper;
import android.text.InputFilter;
import android.text.InputType;
import android.text.Spannable;
import android.text.style.ForegroundColorSpan;
import android.view.LayoutInflater;
import android.view.View;
import android.view.Window;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;

import com.act.MainActivity;
import com.adapter.files.TaxiBidDriverAdapter;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.PaintView;
import com.general.files.SpacesItemDecoration;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.DialogTaxiBidOfferBinding;
import com.buddyverse.main.databinding.DialogTaxiBidOfferRetryBinding;
import com.buddyverse.main.databinding.DialogTaxiBidRaiseBinding;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.AutoFitEditText;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;
import java.util.Timer;
import java.util.TimerTask;

public class RequestNearestCabTaxiBid implements Runnable {

    private final Activity mActivity;
    private final GeneralFunctions generalFunc;
    private DialogTaxiBidOfferBinding binding;
    private Dialog dialogRequestNearestCab;
    private GenerateAlertBox generateAlert;

    @Nullable
    private BottomSheetDialog bottomSheetDialog;
    private String driverIds, cabRequestedJson;

    private TaxiBidDriverAdapter mAdapter;
    private final JSONArray mDriverArr = new JSONArray();
    private final ArrayList<String> vMsgCodeList = new ArrayList<>();
    public String aOfferFare, aDriverId, aMsgCode;
    private final String defaultAmountVal = AutoFitEditText.convertCommaToDecimal("0.00", false);

    public RequestNearestCabTaxiBid(@NonNull Activity activity) {
        this.mActivity = activity;
        if (activity instanceof MainActivity mainActivity) {
            this.generalFunc = mainActivity.generalFunc;
        } else {
            this.generalFunc = MyApp.getInstance().getGeneralFun(activity);
        }
    }

    public void setRequestData(String driverIds, String cabRequestedJson) {
        this.driverIds = driverIds;
        this.cabRequestedJson = cabRequestedJson;
    }

    @SuppressLint("ResourceAsColor")
    @Override
    public void run() {
        dialogRequestNearestCab = new Dialog(mActivity, R.style.NoActionBar);
        dialogRequestNearestCab.requestWindowFeature(Window.FEATURE_NO_TITLE);
        dialogRequestNearestCab.getWindow().setBackgroundDrawable(new ColorDrawable(android.graphics.Color.TRANSPARENT));
        dialogRequestNearestCab.getWindow().setStatusBarColor(ContextCompat.getColor(mActivity, R.color.appThemeColor_1));

        binding = DialogTaxiBidOfferBinding.inflate(LayoutInflater.from(mActivity));

        dialogRequestNearestCab.setContentView(binding.getRoot());
        dialogRequestNearestCab.setCancelable(false);
        dialogRequestNearestCab.setCanceledOnTouchOutside(false);

        binding.headerText.setText(generalFunc.retrieveLangLBl("", "LBL_REQUESTING_TXT"));

        binding.llRadarDraw.addView(new PaintView(mActivity));
        binding.rippleBackground.startRippleAnimation();
        Animation rotate = AnimationUtils.loadAnimation(getApplicationContext(), R.anim.rotate_clockwise);
        rotate.setRepeatCount(1);
        binding.rotationCircle.startAnimation(rotate);

        driverList();

        isOfferDialogShow(true, false);

        openTaxiBidOffer();
        retryBottomDialog();

        LayoutDirection.setLayoutDirection(dialogRequestNearestCab);
        try {
            dialogRequestNearestCab.show();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    private void driverList() {
        mAdapter = new TaxiBidDriverAdapter(generalFunc, mDriverArr, new TaxiBidDriverAdapter.OnClickListener() {
            @Override
            public void onAcceptClick(@NonNull JSONObject itemObject) {
                if (mActivity instanceof MainActivity mainAct) {
                    aOfferFare = generalFunc.getJsonValueStr("OfferFare", itemObject);
                    aDriverId = generalFunc.getJsonValueStr("driverId", itemObject);
                    aMsgCode = generalFunc.getJsonValueStr("vMsgCode", itemObject);
                    mainAct.sendRequestToDrivers(aDriverId, cabRequestedJson);
                }
            }

            @Override
            public void onDeclineClick(int position, @NonNull JSONObject itemObject) {
                mDriverArr.remove(position);
                if (mDriverArr.length() == 0) {
                    isOfferDialogShow(true, false);
                    setVisibleBottomArea(true);
                }
            }
        });
        binding.rvTaxiBidDriverList.addItemDecoration(new SpacesItemDecoration(1, mActivity.getResources().getDimensionPixelSize(R.dimen._10sdp), false));
        binding.rvTaxiBidDriverList.setAdapter(mAdapter);
    }

    public void updateDriverList(JSONObject obj_msg) {
        String vMsgCode = generalFunc.getJsonValueStr("vMsgCode", obj_msg) + "_" + generalFunc.getJsonValueStr("driverId", obj_msg);
        if (Utils.checkText(vMsgCode)) {
            if (vMsgCodeList.size() == 0) {
                tickTick();
            }
            if (!vMsgCodeList.contains(vMsgCode)) {
                try {
                    vMsgCodeList.add(vMsgCode);
                    obj_msg.put("driverTimer", generalFunc.retrieveValue("DRIVER_QUOTATION_ACCEPT_TIME_BID_TAXI"));
                    mDriverArr.put(obj_msg);
                    mAdapter.updateData(mDriverArr);
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }
        if (mDriverArr.length() > 0) {
            isOfferDialogShow(false, true);
        } else {
            isOfferDialogShow(true, false);
        }
    }

    private void tickTick() {
        new Timer().schedule(new TimerTask() {
            @Override
            public void run() {
                mActivity.runOnUiThread(() -> {

                    for (int i = 0; i < mDriverArr.length(); i++) {
                        try {
                            JSONObject obj_msg = generalFunc.getJsonObject(mDriverArr, i);
                            int lastTime = Integer.parseInt(generalFunc.getJsonValueStr("driverTimer", obj_msg));
                            if (lastTime > 0) {
                                obj_msg.put("driverTimer", (lastTime - 1));
                                mDriverArr.put(i, obj_msg);
                            } else {
                                mDriverArr.remove(i);
                            }
                        } catch (JSONException e) {
                            Logger.e("Exception", "::" + e.getMessage());
                        }
                    }
                    mAdapter.updateData(mDriverArr);

                });
            }
        }, 0, 1500);
    }

    @SuppressLint("SetTextI18n")
    private void openTaxiBidOffer() {

        MButton sendToDriverBtn = ((MaterialRippleLayout) binding.sendToDriverBtn).getChildView();
        sendToDriverBtn.setText(generalFunc.retrieveLangLBl("", "LBL_FIND_A_DRIVER_TAXI_BID_TEXT"));
        sendToDriverBtn.setEnabled(false);

        if (generalFunc.isRTLmode()) {
            binding.offerMinus.setRotation(180);
            binding.offerPlus.setRotation(180);
        }

        binding.enterYoreFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_YOUR_FARE_BELOW_TAXI_BID_TEXT"));
        binding.yoreOfferEdit.setHint(defaultAmountVal);
        binding.yoreOfferEdit.setText(defaultAmountVal);
        double fareBidTaxi = 0.00;
        if (mActivity instanceof MainActivity mainActivity) {
            if (mainActivity.cabSelectionFrag != null && mainActivity.cabTypeList != null && mainActivity.cabTypeList.get(mainActivity.cabSelectionFrag.selpos) != null) {
                fareBidTaxi = GeneralFunctions.parseDoubleValue(0.0, mainActivity.cabTypeList.get(mainActivity.cabSelectionFrag.selpos).get("MINIMUM_FARE_BID_TAXI"));
            }
        }
        if (GeneralFunctions.parseDoubleValue(0.0, binding.yoreOfferEdit.getTxt()) >= fareBidTaxi) {
            sendToDriverBtn.setEnabled(true);
        }
        binding.yoreOfferEdit.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        binding.yoreOfferEdit.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        double finalFareBidTaxi = fareBidTaxi;
        double minFare = 0.0;
        binding.yoreOfferEdit.addTextChangedListener(sendToDriverBtn,true);

        binding.offerMinus.setOnClickListener(view -> {
            if (Utils.checkText(binding.yoreOfferEdit) && GeneralFunctions.parseDoubleValue(0, binding.yoreOfferEdit.getTxt()) >= minFare) {
                binding.yoreOfferEdit.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, binding.yoreOfferEdit.getTxt()) - 1));
                if (!(GeneralFunctions.parseDoubleValue(0, binding.yoreOfferEdit.getTxt()) >= minFare)) {
                    binding.yoreOfferEdit.setText(defaultAmountVal);
                }
            }
        });
        binding.offerPlus.setOnClickListener(view -> {
            if (Utils.checkText(binding.yoreOfferEdit)) {
                binding.yoreOfferEdit.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, binding.yoreOfferEdit.getTxt()) + 1));
            } else {
                binding.yoreOfferEdit.setText("1.00");
            }
        });

        binding.offerNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TAX_NOTE_TAXI_BID_TEXT"));

        if (mActivity instanceof MainActivity mainActivity) {
            binding.currencyTxt.setText(generalFunc.getJsonValueStr("vCurrencyPassenger", mainActivity.obj_userProfile));

            if (mainActivity.cabSelectionFrag != null && mainActivity.cabTypeList.get(mainActivity.cabSelectionFrag.selpos) != null) {
                binding.ivFareHelp.setOnClickListener(v -> mainActivity.cabSelectionFrag.openFareDetailsDilaog(mainActivity.cabSelectionFrag.selpos));

                String first = generalFunc.retrieveLangLBl("", "LBL_RECOMMENDED_PRICE_TAXI_BID_TEXT") + ": ";
                String next = generalFunc.convertNumberWithRTL(mainActivity.cabTypeList.get(mainActivity.cabSelectionFrag.selpos).get("total_fare"));

                binding.noteMsgTxt.setText(first + next, TextView.BufferType.SPANNABLE);

                Spannable s = (Spannable) binding.noteMsgTxt.getText();
                int start = first.length();
                int end = start + next.length();
                s.setSpan(new ForegroundColorSpan(ContextCompat.getColor(mActivity, R.color.appThemeColor_1)), start, end, Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);
            } else {
                binding.ivFareHelp.setVisibility(View.GONE);
            }
        }

        sendToDriverBtn.setOnClickListener(v -> {
            if (GeneralFunctions.parseDoubleValue(0, binding.yoreOfferEdit.getTxt()) < finalFareBidTaxi) {
                raiseBottomDialog(finalFareBidTaxi, sendToDriverBtn);
            } else {
                sendToDriver(sendToDriverBtn);
            }
        });

        binding.cancelBtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TXT"));
        binding.cancelBtnTxt.setOnClickListener(v -> cancelRequestConfirm());
    }

    private void sendToDriver(MButton sendToDriverBtn) {
        sendToDriverBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CHANGE_FARE_TAXI_BID_TXT"));

        isOfferDialogShow(false, false);
        if (mActivity instanceof MainActivity mainActivity) {
            mainActivity.requestPickUp();
        }
        new Handler(Looper.myLooper()).postDelayed(() -> sendToDriverBtn.setEnabled(false), 1111);
    }

    @SuppressLint("SetTextI18n")
    private void raiseBottomDialog(double finalFareBidTaxi, MButton sendToDriverBtn) {
        BottomSheetDialog bottomDialog = new BottomSheetDialog(mActivity);
        DialogTaxiBidRaiseBinding dialogBinding = DialogTaxiBidRaiseBinding.inflate(LayoutInflater.from(mActivity));
        bottomDialog.setContentView(dialogBinding.getRoot());

        View bottomSheetView = bottomDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(mActivity.getResources().getColor(android.R.color.transparent));

        dialogBinding.cancelBtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TXT"));

        MButton raiseToBtn = ((MaterialRippleLayout) dialogBinding.raiseToBtn).getChildView();
        if (mActivity instanceof MainActivity mainActivity) {
            if (mainActivity.cabSelectionFrag != null && mainActivity.cabTypeList.get(mainActivity.cabSelectionFrag.selpos) != null) {

                String titleFirst = generalFunc.retrieveLangLBl("", "LBL_RAISE_YOUR_FARE_DETAILS_TAXI_BID_TEXT") + " ";
                String titleNext = generalFunc.convertNumberWithRTL(mainActivity.cabTypeList.get(mainActivity.cabSelectionFrag.selpos).get("total_fare"));

                dialogBinding.headerTitleText.setText(titleFirst + titleNext, TextView.BufferType.SPANNABLE);

                Spannable titleS = (Spannable) dialogBinding.headerTitleText.getText();
                int titleStart = titleFirst.length();
                int titleEnd = titleStart + titleNext.length();
                titleS.setSpan(new ForegroundColorSpan(ContextCompat.getColor(mActivity, R.color.appThemeColor_1)), titleStart, titleEnd, Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);

                ////
                String first = generalFunc.retrieveLangLBl("", "LBL_WE_RECOMMEND_FARE_TAXI_BID_TXT") + " ";
                String next = generalFunc.convertNumberWithRTL(mainActivity.cabTypeList.get(mainActivity.cabSelectionFrag.selpos).get("MINIMUM_FARE_BID_TAXI_WITH_SYMBOL"));

                dialogBinding.minFareMsgTxt.setText(first + next, TextView.BufferType.SPANNABLE);

                Spannable s = (Spannable) dialogBinding.minFareMsgTxt.getText();
                int start = first.length();
                int end = start + next.length();
                s.setSpan(new ForegroundColorSpan(ContextCompat.getColor(mActivity, R.color.appThemeColor_1)), start, end, Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);

                raiseToBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RAISE_TO_TAXI_BID_TEXT") + " " + next);
            }
        }

        raiseToBtn.setOnClickListener(v -> {
            binding.yoreOfferEdit.setText("" + finalFareBidTaxi);
            bottomDialog.dismiss();
            sendToDriver(sendToDriverBtn);
        });
        dialogBinding.cancelBtnTxt.setOnClickListener(v -> {
            bottomDialog.dismiss();
        });
        bottomDialog.setOnDismissListener(dialog -> {
            // Instructions on bottomSheetDialog Dismiss
        });
        bottomDialog.show();
    }

    public String getOfferFareValue() {
        if (dialogRequestNearestCab != null && dialogRequestNearestCab.isShowing()) {
            if (Utils.checkText(binding.yoreOfferEdit)) {
                return binding.yoreOfferEdit.getTxt();
            }
        }
        return "";
    }

    private void isOfferDialogShow(boolean isOfferDialogShow, boolean isDriverList) {
        binding.findingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_OFFER_TAXI_BID_TEXT"));
        if (isOfferDialogShow) {
            binding.headerText.setVisibility(View.INVISIBLE);
            binding.radarDrawArea.setVisibility(View.INVISIBLE);
            binding.offerArea.setVisibility(View.VISIBLE);

        } else if (isDriverList) {
            binding.headerText.setVisibility(View.VISIBLE);
            binding.radarDrawArea.setVisibility(View.VISIBLE);
            binding.offerArea.setVisibility(View.GONE);
            binding.findingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FINDING_DRIVERS_TAXI_BID_TEXT"));
        } else {
            binding.headerText.setVisibility(View.VISIBLE);
            binding.radarDrawArea.setVisibility(View.VISIBLE);
            binding.offerArea.setVisibility(View.VISIBLE);
        }
    }

    private void retryBottomDialog() {
        bottomSheetDialog = new BottomSheetDialog(mActivity);
        DialogTaxiBidOfferRetryBinding dialogBinding = DialogTaxiBidOfferRetryBinding.inflate(LayoutInflater.from(mActivity));
        bottomSheetDialog.setContentView(dialogBinding.getRoot());

        View bottomSheetView = bottomSheetDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(mActivity.getResources().getColor(android.R.color.transparent));

        dialogBinding.noDriverText.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE_NO_DRIVER_REQUEST"));
        MButton retryBtn = ((MaterialRippleLayout) dialogBinding.retryBtn).getChildView();
        MButton cancelBtn = ((MaterialRippleLayout) dialogBinding.cancelBtn).getChildView();
        retryBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));
        cancelBtn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TXT"));

        dialogBinding.retryBtn.setOnClickListener(v -> {
            if (mActivity instanceof MainActivity mainActivity) {
                mainActivity.retryReqBtnPressed(driverIds, cabRequestedJson);
            }
            bottomSheetDialog.dismiss();
            isOfferDialogShow(false, false);
        });
        dialogBinding.cancelBtn.setOnClickListener(v -> {
            cancelRequestConfirm();
            bottomSheetDialog.dismiss();
        });

        bottomSheetDialog.setOnDismissListener(dialog -> {
            // Instructions on bottomSheetDialog Dismiss
        });
    }

    public void setVisibleBottomArea(boolean isVisible) {
        if (bottomSheetDialog != null) {
            bottomSheetDialog.dismiss();
            if (isVisible) {
                isOfferDialogShow(true, false);
                bottomSheetDialog.show();
            }
        }
    }

    public void dismissDialog() {
        if (dialogRequestNearestCab != null && dialogRequestNearestCab.isShowing()) {
            dialogRequestNearestCab.dismiss();
        }
    }

    private void cancelRequestConfirm() {
        if (generateAlert != null) {
            generateAlert.closeAlertBox();
            generateAlert = null;
        }

        if (binding.headerText.getVisibility() == View.INVISIBLE
                && binding.offerArea.getVisibility() == View.VISIBLE &&
                vMsgCodeList.size() == 0) {
            dismissDialog();
            return;
        }

        generateAlert = new GenerateAlertBox(mActivity);
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
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
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_REQUEST_CANCEL_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_TRIP_CANCEL_CONFIRM_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));
        generateAlert.showAlertBox();
    }

    private void cancelRequest() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "cancelCabRequest");
        parameters.put("iUserId", generalFunc.getMemberId());

        ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {

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

    private void manageAfterCancelCabRequest(String message) {
        dismissDialog();
        if (mActivity instanceof MainActivity mainActivity) {
            mainActivity.releaseScheduleNotificationTask();
        }

        if (Utils.checkText(message)) {
            releaseResources();
            generalFunc.restartApp();
        } else {
            if (mActivity instanceof MainActivity) {
                releaseResources();
                MyApp.getInstance().restartWithGetDataApp();
            }
        }
    }

    private void releaseResources() {
        if (mActivity instanceof MainActivity mainAct) {
            if (mainAct.mainHeaderFrag != null) {
                mainAct.mainHeaderFrag.releaseResources();
                mainAct.mainHeaderFrag = null;
            }

            if (mainAct.cabSelectionFrag != null) {
                mainAct.stopOverPointsList.clear();
                mainAct.cabSelectionFrag.releaseResources();
                mainAct.cabSelectionFrag = null;
            }
        }
    }
}

