package com.act.rideSharingPro;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.ContactUsActivity;
import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.general.call.CommunicationManager;
import com.general.call.DefaultCommunicationHandler;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideBookDetailsBinding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.LoadImageGlide;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RideBookDetails extends ParentActivity {

    private ActivityRideBookDetailsBinding binding;
    private HashMap<String, String> myRideDataHashMap;

    private AlertDialog dialogRideDecline;
    private ImageView filterImageview;
    private MButton cancelRideBtn, continueBtn;
    private int selCurrentPosition = -1;
    private boolean isDeclineCancel = false;
    private String cImage = "";
    private String callingMethod;
    private BottomSheetDialog bottomSheetDialog;
    private Handler carImgHandler;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_book_details);

        myRideDataHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("myRideDataHashMap");
        if (myRideDataHashMap == null) {
            return;
        }

        initialization();
        rideDetails();

        setPaymentData();
        driverDetails();
        carDetails();


        if (myRideDataHashMap.containsKey("eStatus")) {

            isDeclineCancel = myRideDataHashMap.get("eStatus").equalsIgnoreCase("Declined") || myRideDataHashMap.get("eStatus").equalsIgnoreCase("Cancelled");
            if (myRideDataHashMap.containsKey("tCancelReason")) {
                isDeclineCancel = Utils.checkText(myRideDataHashMap.get("tCancelReason"));
            }

            if (isDeclineCancel) {
                cancelRideBtn.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_REASON"));
            } else {
                cancelRideBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CANCEL"));
            }
            binding.cancelRideBtnArea.setVisibility(myRideDataHashMap.get("isCancelbuttonHide").equalsIgnoreCase("Yes") ? View.GONE : View.VISIBLE);
        }
        continueBtn = ((MaterialRippleLayout) binding.continueBtn).getChildView();
        continueBtn.setId(Utils.generateViewId());
        if (getIntent().getBooleanExtra("isSearchView", false)) {
            binding.paymentArea.setVisibility(View.GONE);
            binding.cancelRideBtnArea.setVisibility(View.GONE);

            binding.bottomArea.setVisibility(View.VISIBLE);
            binding.totalHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_TOTAL_PRICE"));
            binding.noOfPassengerText.setText(myRideDataHashMap.get("vNoOfPassengerText"));
            binding.fPriceTxt.setText(myRideDataHashMap.get("fToTalPrice"));

            continueBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CONTINUE"));
            addToClickHandler(continueBtn);
        } else {
            binding.bottomArea.setVisibility(View.GONE);
        }
        filterImageview.setVisibility(generalFunc.getMemberId().equalsIgnoreCase(myRideDataHashMap.get("iDriverId")) ? View.GONE : View.VISIBLE);
        binding.continueBtnArea.setVisibility(generalFunc.getMemberId().equalsIgnoreCase(myRideDataHashMap.get("iDriverId")) ? View.GONE : View.VISIBLE);
    }

    private void initialization() {
        callingMethod = generalFunc.getJsonValue("CALLING_METHOD_RIDE_SHARE", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_RIDE_DETAILS"));
        filterImageview = findViewById(R.id.filterImageview);
        filterImageview.setImageResource(R.drawable.ic_contacus_mail);
        addToClickHandler(filterImageview);

        cancelRideBtn = ((MaterialRippleLayout) binding.cancelRideBtn).getChildView();
        cancelRideBtn.setId(Utils.generateViewId());
        addToClickHandler(cancelRideBtn);
        carImgHandler = new Handler(Looper.getMainLooper());
    }

    private void rideDetails() {
        binding.rideDetailsTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ROUTE_DETAILS"));
        binding.startTimeTxt.setText(myRideDataHashMap.get("StartTime"));
        binding.endTimeTxt.setText(myRideDataHashMap.get("EndTime"));

        if (ServiceModule.EnableRideSharingPro) {
            binding.sLocTagTxt.setText(myRideDataHashMap.get("SourceLocationPoint"));
            binding.eLocTagTxt.setText(myRideDataHashMap.get("DestLocationPoint"));
        }

        binding.startCityTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DETAILS_START_LOC_TXT"));
        binding.endCityTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DETAILS_END_LOC_TXT"));

        binding.startAddressTxt.setText(myRideDataHashMap.get("tStartLocation"));
        binding.endAddressTxt.setText(myRideDataHashMap.get("tEndLocation"));

        dynamicView();

        binding.dateTxt.setText(myRideDataHashMap.get("tDisplayDate"));
        //binding.dateTxt.setText(myRideDataHashMap.get("StartDate"));
        binding.priceTxt.setText(myRideDataHashMap.get("fPrice"));
        binding.priceMsgTxt.setText(myRideDataHashMap.get("PriceLabel"));
    }

    @SuppressLint("SetTextI18n")
    private void dynamicView() {
        // multiStop address
        if (binding.dynamicStopPointView.getChildCount() > 0) {
            binding.dynamicStopPointView.removeAllViewsInLayout();
        }
        JSONArray waypointsArr = generalFunc.getJsonArray(myRideDataHashMap.get("waypoints"));
        if (waypointsArr != null && ServiceModule.EnableRideSharingPro) {
            RideSharingUtils.wayPointsView(this, generalFunc, waypointsArr, binding.dynamicStopPointView);
        }

        // multiStop Fare
        if (binding.multiStopFareDataView.getChildCount() > 0) {
            binding.multiStopFareDataView.removeAllViewsInLayout();
        }
        JSONArray waypointFareArr = generalFunc.getJsonArray(myRideDataHashMap.get("waypointFare"));
        if (waypointFareArr != null && ServiceModule.EnableRideSharingPro) {
            binding.multiStopFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STOP_OVER_POINT_PRICE_RIDE_SHARE_TEXT"));
            for (int i = 0; i < waypointFareArr.length(); i++) {
                JSONObject jobject = generalFunc.getJsonObject(waypointFareArr, i);
                try {
                    String data = Objects.requireNonNull(jobject.names()).getString(0);

                    RideSharingUtils.addSummaryRow(this, generalFunc, binding.multiStopFareDataView, data, jobject.get(data).toString(), false);
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }
        binding.multiStopFareArea.setVisibility(binding.multiStopFareDataView.getChildCount() > 0 ? View.VISIBLE : View.GONE);

        // Driver Preferences
        if (binding.dynamicAboutView.getChildCount() > 0) {
            binding.dynamicAboutView.removeAllViewsInLayout();
        }
        JSONArray aboutArr = generalFunc.getJsonArray(myRideDataHashMap.get("TRAVEL_PREFERENCES_ARR"));
        if (aboutArr != null && ServiceModule.EnableRideSharingPro) {
            binding.aboutHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_RIDE_SHARE_TEXT") + " " + myRideDataHashMap.get("DriverName"));
            RideSharingUtils.preferencesView(this, generalFunc, aboutArr, binding.dynamicAboutView);
        }
        binding.aboutArea.setVisibility(binding.dynamicAboutView.getChildCount() > 0 ? View.VISIBLE : View.GONE);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == Activity.RESULT_OK && data != null) {
            new ActUtils(this).setOkResult(data.getExtras());
            finish();
        }
    }

    @SuppressLint("SetTextI18n")
    private void setPaymentData() {
        binding.paymentHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PYMENT_DETAILS"));
        binding.paymentModeHTxt.setText(myRideDataHashMap.get("PaymentModeTitle") + ": ");
        binding.paymentModeVTxt.setText(myRideDataHashMap.get("PaymentModeLabel"));

        JSONArray summaryArray = generalFunc.getJsonArray(myRideDataHashMap.get("PriceBreakdown"));
        if (summaryArray != null) {

            if (binding.summaryData.getChildCount() > 0) {
                binding.summaryData.removeAllViewsInLayout();
            }

            for (int i = 0; i < summaryArray.length(); i++) {
                JSONObject jobject = generalFunc.getJsonObject(summaryArray, i);
                try {
                    String data = Objects.requireNonNull(jobject.names()).getString(0);

                    RideSharingUtils.addSummaryRow(this, generalFunc, binding.summaryData, data, jobject.get(data).toString(), (summaryArray.length() - 1) == i);
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }
        binding.summaryDataArea.setVisibility(0 < binding.summaryData.getChildCount() ? View.VISIBLE : View.GONE);
    }

    private void driverDetails() {
        binding.driverHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_DETAILS_TITLE"));
        binding.rideDriverName.setText(myRideDataHashMap.get("DriverName"));

        if (myRideDataHashMap.containsKey("isContactToDriver") && Objects.requireNonNull(myRideDataHashMap.get("isContactToDriver")).equalsIgnoreCase("No")) {
            binding.rideDriverPhone.setVisibility(View.GONE);
        } else {
            binding.rideDriverPhone.setVisibility(View.VISIBLE);
            binding.rideDriverPhone.setText(myRideDataHashMap.get("DriverPhone"));
            binding.rideDriverPhone.setOnClickListener(view -> {
                MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                        .setToMemberId(myRideDataHashMap.get("iDriverId"))
                        .setToMemberName(myRideDataHashMap.get("DriverName"))
                        .setPhoneNumber(myRideDataHashMap.get("DriverPhone"))
                        .setToMemberType(Utils.CALLTOPASSENGER)
                        .setToMemberImage(myRideDataHashMap.get("DriverImg"))
                        .setMedia(CommunicationManager.MEDIA_TYPE)
                        .build();
                if (callingMethod.equalsIgnoreCase("VOIP")) {
                    CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.PHONE_CALL);
                } else if (callingMethod.equalsIgnoreCase("VIDEOCALL")) {
                    CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.VIDEO_CALL);
                } else if (callingMethod.equalsIgnoreCase("VOIP-VIDEOCALL")) {
                    CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.BOTH_CALL);
                } else if (!Utils.checkText(callingMethod) || callingMethod.equalsIgnoreCase("NORMAL")) {
                    DefaultCommunicationHandler.getInstance().executeAction(MyApp.getInstance().getCurrentAct(), CommunicationManager.TYPE.PHONE_CALL, mDataProvider);
                }
            });
        }

        String dImage = myRideDataHashMap.get("DriverImg");
        if (!Utils.checkText(dImage)) {
            dImage = "Temp";
        }
        new LoadImageGlide.builder(this, LoadImageGlide.bind(dImage), binding.rideDriverImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        if (ServiceModule.EnableRideSharingPro && myRideDataHashMap.containsKey("IS_RATING_SHOW") && Objects.requireNonNull(myRideDataHashMap.get("IS_RATING_SHOW")).equalsIgnoreCase("Yes")) {
            binding.rideSharingRatingBar.setVisibility(View.VISIBLE);

            String setRating = myRideDataHashMap.get("rating");
            if (Utils.checkText(setRating)) {
                binding.rideSharingRatingBar.setRating(GeneralFunctions.parseFloatValue(0, setRating));
                binding.rideSharingRatingBar.setIndicator(true);
            } else {
                binding.rideSharingRatingBar.setOnRatingBarChangeListener((simpleRatingBar, v, b) -> {
                    if (bottomSheetDialog != null && bottomSheetDialog.isShowing()) {
                        return;
                    }
                    HashMap<String, String> dataHashMap = new HashMap<>();
                    dataHashMap.put("toName", myRideDataHashMap.get("DriverName"));
                    dataHashMap.put("iBookingId", myRideDataHashMap.get("iBookingId"));
                    dataHashMap.put("FromUserType", "rider");
                    dataHashMap.put("ToUserId", myRideDataHashMap.get("iDriverId"));
                    bottomSheetDialog = RideSharingUtils.ratingBottomDialog(this, generalFunc, dataHashMap, binding.rideSharingRatingBar);
                });
            }
        } else {
            binding.rideSharingRatingBar.setVisibility(View.GONE);
        }
    }

    @SuppressLint("SetTextI18n")
    private void carDetails() {
        binding.carDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CAR_DETAILS_TITLE"));
        binding.addNotesHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ADDITIONAL_NOTES_TXT"));

        JSONObject carDetailsObj = generalFunc.getJsonObject(myRideDataHashMap.get("carDetails"));
        binding.carModelColorTxt.setText(generalFunc.getJsonValueStr("cModel", carDetailsObj));
        binding.carMakeTxt.setText(generalFunc.getJsonValueStr("cMake", carDetailsObj));
        binding.carNumberPlateTxt.setText(generalFunc.getJsonValueStr("cNumberPlate", carDetailsObj));
        binding.addNotesMsgHTxt.setText(generalFunc.getJsonValueStr("cNote", carDetailsObj));
        if (carImgHandler == null) {
            carImgHandler = new Handler(Looper.getMainLooper());
        }

        carImgHandler.postDelayed(() -> {
            int radius = getResources().getDimensionPixelSize(R.dimen._7sdp);
            binding.setImgView.setShapeAppearanceModel(binding.setImgView.getShapeAppearanceModel().toBuilder().setTopRightCorner(CornerFamily.ROUNDED, radius).setTopLeftCorner(CornerFamily.ROUNDED, radius).build());
            cImage = generalFunc.getJsonValueStr("cImage", carDetailsObj);
            String cImage_view = Utils.getResizeImgURL(RideBookDetails.this, Objects.requireNonNull(generalFunc.getJsonValueStr("cImage", carDetailsObj)), binding.setImgView.getMeasuredWidth(), binding.setImgView.getMeasuredHeight());
            if (!Utils.checkText(cImage)) {
                cImage = "Temp";
            }
            new LoadImageGlide.builder(RideBookDetails.this, LoadImageGlide.bind(cImage_view), binding.setImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
        }, 20);
        addToClickHandler(binding.setImgView);
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.setImgView.getId()) {
            new ActUtils(this).openURL(cImage);
        } else if (i == filterImageview.getId()) {
            new ActUtils(this).startAct(ContactUsActivity.class);
        } else if (i == cancelRideBtn.getId()) {
            if (myRideDataHashMap.containsKey("eStatus")) {
                if (isDeclineCancel) {
                    generalFunc.showGeneralMessage("", myRideDataHashMap.get("tCancelReason"));
                } else {
                    showRideDeclineReasonsAlert();
                }
            } else {
                Bundle bn = new Bundle();
                bn.putSerializable("myRideDataHashMap", myRideDataHashMap);
                new ActUtils(this).startActForResult(RideBookSummary.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
            }
        } else if (i == continueBtn.getId()) {
            Bundle bn = new Bundle();
            bn.putSerializable("myRideDataHashMap", myRideDataHashMap);
            new ActUtils(this).startActForResult(RideBookSummary.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
        }
    }

    private void showRideDeclineReasonsAlert() {
        if (dialogRideDecline != null) {
            if (dialogRideDecline.isShowing()) {
                dialogRideDecline.dismiss();
            }
            dialogRideDecline = null;
        }
        selCurrentPosition = -1;
        AlertDialog.Builder builder = new AlertDialog.Builder(this);

        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        int size10sdp = (int) getResources().getDimension(R.dimen._10sdp);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, size10sdp, 0);
        } else {
            reasonBox.setPaddings(size10sdp, 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));
        ArrayList<HashMap<String, String>> sub_list = new ArrayList<>();

        MTextView cancelTxt = dialogView.findViewById(R.id.cancelTxt);
        MTextView submitTxt = dialogView.findViewById(R.id.submitTxt);
        MTextView subTitleTxt = dialogView.findViewById(R.id.subTitleTxt);
        ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);

        subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CANCEL_RIDE"));

        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));
        MTextView declinereasonBox = dialogView.findViewById(R.id.declinereasonBox);
        declinereasonBox.setText("-- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_CANCEL_REASON") + " --");
        submitTxt.setClickable(false);
        submitTxt.setTextColor(getResources().getColor(R.color.gray_holo_light));

        submitTxt.setOnClickListener(v -> {
            if (selCurrentPosition == -1) {
                return;
            }
            if (!Utils.checkText(reasonBox) && selCurrentPosition == (sub_list.size() - 1)) {
                errorTextView.setVisibility(View.VISIBLE);
                errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                return;
            }
            cancelRideSharing(myRideDataHashMap.get("iBookingId"), sub_list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox));
            dialogRideDecline.dismiss();
        });
        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(this);
            errorTextView.setVisibility(View.GONE);
            dialogRideDecline.dismiss();
        });

        cancelImg.setOnClickListener(v -> {
            Utils.hideKeyboard(this);
            errorTextView.setVisibility(View.GONE);
            dialogRideDecline.dismiss();
        });

        declinereasonBox.setOnClickListener(v -> {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "GetCancelReasons");
            parameters.put("iMemberId", generalFunc.getMemberId());
            parameters.put("eUserType", Utils.app_type);
            parameters.put("eJobType", myRideDataHashMap.get("eJobType"));

            parameters.put("iPublishedRideId", myRideDataHashMap.get("iPublishedRideId"));

            ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
                sub_list.clear();
                if (Utils.checkText(responseString)) {

                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
                        if (arr_msg != null) {
                            int arrSize = arr_msg.length();
                            for (int i = 0; i < arrSize; i++) {
                                JSONObject obj_tmp = generalFunc.getJsonObject(arr_msg, i);
                                HashMap<String, String> datamap = new HashMap<>();
                                datamap.put("title", generalFunc.getJsonValueStr("vTitle", obj_tmp));
                                datamap.put("id", generalFunc.getJsonValueStr("iCancelReasonId", obj_tmp));
                                sub_list.add(datamap);
                            }
                            HashMap<String, String> othermap = new HashMap<>();
                            othermap.put("title", generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
                            othermap.put("id", "");
                            sub_list.add(othermap);

                            OpenListView.getInstance(this, generalFunc.retrieveLangLBl("", "LBL_SELECT_REASON"), sub_list, OpenListView.OpenDirection.CENTER, true, position -> {
                                selCurrentPosition = position;
                                HashMap<String, String> mapData = sub_list.get(position);
                                errorTextView.setVisibility(View.GONE);
                                declinereasonBox.setText(mapData.get("title"));
                                if (selCurrentPosition == (sub_list.size() - 1)) {
                                    reasonBox.setVisibility(View.VISIBLE);
                                    commentArea.setVisibility(View.VISIBLE);
                                } else {
                                    commentArea.setVisibility(View.GONE);
                                    reasonBox.setVisibility(View.GONE);
                                }
                                submitTxt.setClickable(true);
                                submitTxt.setTextColor(getResources().getColor(R.color.white));
                            }).show(selCurrentPosition, "title");
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
                        }
                    } else {
                        String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                        if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY) || message.equals("LBL_SERVER_COMM_ERROR")) {

                            MyApp.getInstance().restartWithGetDataApp();
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                        }
                    }
                } else {
                    generalFunc.showError();
                }
            });
        });
        dialogRideDecline = builder.create();
        dialogRideDecline.setCancelable(false);
        dialogRideDecline.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(this, R.drawable.all_roundcurve_card));
        dialogRideDecline.show();
    }

    private void cancelRideSharing(String iBookingId, String iCancelReasonId, String reason) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CancelRideShareBooking");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("iBookingId", iBookingId);
        parameters.put("iCancelReasonId", iCancelReasonId);
        parameters.put("tCancelReason", reason);

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), "", generalFunc.retrieveLangLBl("", "LBL_OK"), i -> {
                        (new ActUtils(this)).setOkResult();
                        finish();
                    });
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }
}