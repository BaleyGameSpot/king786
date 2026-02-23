package com.act.rideSharingPro;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.ContactUsActivity;
import com.act.MyWalletActivity;
import com.act.rideSharingPro.adapter.RideDocumentAdapter;
import com.act.rideSharingPro.adapter.RideMyPassengerAdapter;
import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.SpacesItemDecoration;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideMyDetailsBinding;
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

public class RideMyDetails extends ParentActivity {

    private ActivityRideMyDetailsBinding binding;
    private HashMap<String, String> myRideDataHashMap;

    private RideMyPassengerAdapter rideMyPassengerAdapter;
    private final ArrayList<HashMap<String, String>> mRideMyPassengerList = new ArrayList<>();

    private AlertDialog dialogRideDecline;
    private int selCurrentPosition = -1;
    private MButton cancelRideBtn;
    private ImageView filterImageview;
    private String cImage = "";
    public ArrayList<HashMap<String, String>> verificationDocumentListRMD = new ArrayList<>();
    private RideDocumentAdapter rideDocumentAdapter;
    private boolean isRideCancelled;
    private Handler carImgHandler;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_my_details);

        myRideDataHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("myRideDataHashMap");
        if (myRideDataHashMap == null) {
            return;
        }

        initialization();
        rideDetails();

        setPassengerListData();
        setPassengerList(myRideDataHashMap);

        carDetails();
        DocumentDetails(myRideDataHashMap);

        getRideMyPassengerList(false);
    }

    private void DocumentDetails(HashMap<String, String> myRideDataHashMap) {
        try {
            if (Utils.checkText(myRideDataHashMap.get("tDocumentIds"))) {
                JSONArray docArray = new JSONArray(myRideDataHashMap.get("tDocumentIds"));
                verificationDocumentListRMD.clear();
                if (docArray.length() > 0) {
                    MyUtils.createArrayListJSONArray(generalFunc, verificationDocumentListRMD, docArray);
                    binding.docDetailsLayout.setVisibility(View.VISIBLE);
                } else {
                    binding.docDetailsLayout.setVisibility(View.GONE);
                }
                rideDocumentAdapter.updateData();
            } else {
                binding.docDetailsLayout.setVisibility(View.GONE);
            }
        } catch (JSONException e) {
            binding.docDetailsLayout.setVisibility(View.GONE);
            throw new RuntimeException(e);
        }
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_RIDE_DETAILS"));
        filterImageview = findViewById(R.id.filterImageview);
        filterImageview.setVisibility(View.VISIBLE);
        filterImageview.setImageResource(R.drawable.ic_contacus_mail);
        addToClickHandler(filterImageview);
        if (myRideDataHashMap.containsKey("eStatus")) {
            isRideCancelled = Objects.requireNonNull(myRideDataHashMap.get("eStatus")).equalsIgnoreCase("Cancelled");
        }
        isRideCancelled = !Objects.requireNonNull(myRideDataHashMap.get("tCancelReason")).equalsIgnoreCase("");
        cancelRideBtn = ((MaterialRippleLayout) binding.cancelRideBtn).getChildView();
        if (isRideCancelled) {
            cancelRideBtn.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_REASON"));
        } else {
            cancelRideBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CANCEL"));
        }
        cancelRideBtn.setId(Utils.generateViewId());
        addToClickHandler(cancelRideBtn);
        rideDocumentAdapter = new RideDocumentAdapter(verificationDocumentListRMD, new RideDocumentAdapter.OnItemClickListener() {
            @Override
            public void onItemClickList(HashMap<String, String> mapData) {
                Bundle bn = new Bundle();
                bn.putBoolean("isOnlyShow", true);
                bn.putSerializable("documentDataHashMap", mapData);
                new ActUtils(RideMyDetails.this).startActForResult(RideUploadDocActivity.class, bn, Utils.UPLOAD_DOC_REQ_CODE);
            }

            @Override
            public void onUpdateDocumentIds(String documentIds) {
            }
        });
        binding.rvVerificationDocument.setAdapter(rideDocumentAdapter);
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
        binding.priceTxt.setText(myRideDataHashMap.get("fPrice"));
        binding.priceMsgTxt.setText(myRideDataHashMap.get("PriceLabel"));

        binding.verifyDocHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DOCUMET"));
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

    private void setPassengerListData() {
        binding.passengerHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PASSENGER_DETAILS"));
        binding.noPassengerHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_NO_REQUESTS_MSG"));

        rideMyPassengerAdapter = new RideMyPassengerAdapter(generalFunc, mRideMyPassengerList, new RideMyPassengerAdapter.OnItemClickListener() {
            @Override
            public void onViewReasonClick(HashMap<String, String> hashMap) {
                generalFunc.showGeneralMessage("", hashMap.get("DeclineReason"));
            }

            @Override
            public void onDeclineClick(HashMap<String, String> hashMap, int position) {
                showRideDeclineReasonsAlert("Yes", hashMap);
            }

            @Override
            public void onAcceptClick(HashMap<String, String> hashMap, int position) {
                setBookingsStatus("Approved", hashMap, "", "");
            }
        });
        binding.rvRidePassengerList.addItemDecoration(new SpacesItemDecoration(1, getResources().getDimensionPixelSize(R.dimen._12sdp), false));
        binding.rvRidePassengerList.setAdapter(rideMyPassengerAdapter);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void setPassengerList(@NonNull HashMap<String, String> mHashMap) {
        // Passenger List
        mRideMyPassengerList.clear();
        MyUtils.createArrayListJSONArray(generalFunc, mRideMyPassengerList, generalFunc.getJsonArray(mHashMap.get("BookingList")));
        binding.noPassengerHTxt.setVisibility(View.GONE);
        rideMyPassengerAdapter.notifyDataSetChanged();
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
            String cImage_view = Utils.getResizeImgURL(RideMyDetails.this, Objects.requireNonNull(generalFunc.getJsonValueStr("cImage", carDetailsObj)), binding.setImgView.getMeasuredWidth(), binding.setImgView.getMeasuredHeight());
            if (!Utils.checkText(cImage)) {
                cImage = "Temp";
            }
            new LoadImageGlide.builder(RideMyDetails.this, LoadImageGlide.bind(cImage_view), binding.setImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
        }, 20);
        addToClickHandler(binding.setImgView);
    }

    @SuppressLint("SetTextI18n")
    private void showRideDeclineReasonsAlert(String isDeclined, @Nullable HashMap<String, String> hashMap) {
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
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        MTextView subTitleTxt = dialogView.findViewById(R.id.subTitleTxt);
        ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);

        if (hashMap != null && isDeclined.equalsIgnoreCase("Yes")) {
            subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DECLINE_RIDE"));
        } else {
            subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CANCEL_RIDE"));
        }

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
            if (hashMap != null && isDeclined.equalsIgnoreCase("Yes")) {
                setBookingsStatus("Declined", hashMap, sub_list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox));
            } else {
                cancelRideSharing(myRideDataHashMap.get("iPublishedRideId"), sub_list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox));
            }
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
            if (hashMap != null && Utils.checkText(isDeclined)) {
                parameters.put("PublishedRideDecline", isDeclined);
            }

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
                        if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY)
                                || message.equals("LBL_SERVER_COMM_ERROR")) {

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
        Objects.requireNonNull(dialogRideDecline.getWindow()).setBackgroundDrawable(ContextCompat.getDrawable(this, R.drawable.all_roundcurve_card));
        dialogRideDecline.show();
    }

    private void cancelRideSharing(String iPublishedRideId, String iCancelReasonId, String reason) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CancelPublishRide");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("iPublishedRideId", iPublishedRideId);
        parameters.put("iCancelReasonId", iCancelReasonId);
        parameters.put("tCancelReason", reason);

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), i -> {
                        new ActUtils(RideMyDetails.this).setOkResult();
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

    @SuppressLint("NotifyDataSetChanged")
    private void setBookingsStatus(String status, HashMap<String, String> hashMap, String iCancelReasonId, String reason) {

        if (binding.loading.getVisibility() == View.VISIBLE) {
            return;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateBookingsStatus");
        parameters.put("eStatus", status);

        parameters.put("iPublishedRideId", myRideDataHashMap.get("iPublishedRideId"));
        parameters.put("iBookingId", hashMap.get("iBookingId"));

        if (status.equalsIgnoreCase("Declined")) {
            parameters.put("iCancelReasonId", iCancelReasonId);
            parameters.put("tCancelReason", reason);
        }

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.isEmpty()) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    getRideMyPassengerList(true);
                    (new ActUtils(this)).setOkResult();
                } else {
                    if (generalFunc.getJsonValue("LOW_WALLET_BAL", responseString).equalsIgnoreCase("Yes")) {
                        generalFunc.showGeneralMessage(
                                generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"),
                                generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)),
                                generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"),
                                generalFunc.retrieveLangLBl("", "LBL_ADD_NOW"), button_Id -> {
                                    if (button_Id == 1) {
                                        new ActUtils(this).startAct(MyWalletActivity.class);
                                    }
                                });
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getRideMyPassengerList(boolean isLoader) {

        if (isLoader) {
            if (binding.loading.getVisibility() == View.VISIBLE) {
                return;
            }
            binding.loading.setVisibility(View.VISIBLE);
        }
        binding.mainArea.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetPublishedRides");
        parameters.put("iPublishedRideId", myRideDataHashMap.get("iPublishedRideId"));

        ApiHandler.execute(this, parameters, responseString -> {

            binding.mainArea.setVisibility(View.VISIBLE);
            binding.loading.setVisibility(View.GONE);
            binding.docVerifyMesTxt.setVisibility(View.GONE);

            if (responseString != null && !responseString.isEmpty()) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    JSONArray dataArray = generalFunc.getJsonArray(Utils.message_str, responseString);

                    final ArrayList<HashMap<String, String>> mRideMyList = new ArrayList<>();
                    MyUtils.createArrayListJSONArray(generalFunc, mRideMyList, dataArray);
                    if (!mRideMyList.isEmpty()) {
                        myRideDataHashMap = mRideMyList.get(0);
                        if (myRideDataHashMap != null) {
                            setPassengerList(myRideDataHashMap);
                        }
                        binding.noPassengerHTxt.setVisibility(!mRideMyPassengerList.isEmpty() ? View.GONE : View.VISIBLE);
                    }

                    binding.cancelRideBtnArea.setVisibility(Objects.requireNonNull(myRideDataHashMap.get("isCancelbuttonHide")).equalsIgnoreCase("Yes") ? View.GONE : View.VISIBLE);

                    if (dataArray != null && dataArray.length() > 0) {
                        if (generalFunc.getJsonValueStr("eApproveDoc", generalFunc.getJsonObject(dataArray, 0)).equalsIgnoreCase("No")) {
                            binding.docVerifyMesTxt.setVisibility(View.VISIBLE);
                            binding.docVerifyMesTxt.setText(generalFunc.getJsonValueStr("DocReviewMes", generalFunc.getJsonObject(dataArray, 0)));
                        }
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == cancelRideBtn.getId()) {
            if (isRideCancelled) {
                generalFunc.showGeneralMessage("", myRideDataHashMap.get("tCancelReason"));
            } else {
                showRideDeclineReasonsAlert("", null);
            }
        } else if (i == filterImageview.getId()) {
            new ActUtils(this).startAct(ContactUsActivity.class);
        } else if (i == binding.setImgView.getId()) {
            new ActUtils(this).openURL(cImage);
        }
    }
}