package com.act.parking;

import android.app.Activity;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.Toolbar;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.dialogs.MyCommonDialog;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityReviewParkingBookingBinding;
import com.service.handler.ApiHandler;
import com.utils.JSONUtils;
import com.utils.LoadImageGlide;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class ReviewOrCancelParkingBookingActivity extends ParentActivity {

    public ActivityReviewParkingBookingBinding binding;
    public Toolbar mToolbar;
    String vLatitude = "", vLongitude = "";
    private String vehicleId = "", bookingId = "", vehicleSizeId = "";
    String ratingValue = "";
    private final ArrayList<HashMap<String, String>> vehiclesData = new ArrayList<>();
    private boolean isCancel = false;

    int selCurrentPosition = -1;
    AlertDialog dialog_declineOrder;
    private String parkingSpaceId = "";

    private boolean isVehicleList = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_review_parking_booking);

        initialize();
    }

    private void initialize() {

        mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        addToClickHandler(binding.rateBtn);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        float radius = getResources().getDimension(R.dimen._7sdp);
        binding.carImgView.setShapeAppearanceModel(binding.carImgView.getShapeAppearanceModel().toBuilder().setTopLeftCorner(CornerFamily.ROUNDED, radius).setTopRightCorner(CornerFamily.ROUNDED, radius).build());
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_REVIEW_BOOKING_INFO_TITLE"));

        binding.cashTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CASH_TXT"));
        binding.selectTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT"));
        binding.selectTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT"));
        binding.carNotFoundTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_CAR_DETAILS_NOT_FOUND_TXT"));
        binding.carNotFoundSubTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_NO_USER_VEHICLES_FOUND"));
        binding.canceltxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_BOOKING"));
        binding.paymentModeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PYMENT_MODE"));
        binding.ratingHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_RATINGS_REVIEW_TXT"));
        binding.EtReview.setHint(generalFunc.retrieveLangLBl("", "LBL_PARKING_RATINGS_REVIEW_PLACEHOLDER_TXT"));
        binding.rateBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RATE"));
        binding.bookingCancelledHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_BOOKING_CANCELLED_MSG"));
        addToClickHandler(binding.btnPayAndReserve);
        addToClickHandler(binding.btnType2);

        binding.ratingBar.setOnRatingBarChangeListener((simpleRatingBar, rating, fromUser) -> ratingValue = String.valueOf(rating));

        if (getIntent().hasExtra("responseStr")) {
            isCancel = getIntent().hasExtra("isCancel");
            setResponse(getIntent().getStringExtra("responseStr"));
        } else if (getIntent().hasExtra("CallApi")) {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "FetchParkingSpaceDetails");
            parameters.put("iDurationId", getIntent().getStringExtra("duration"));
            parameters.put("ArrivalDate", getIntent().getStringExtra("ArrivalDate"));
            parameters.put("vLatitude", getIntent().getStringExtra("bookingLatitude"));
            parameters.put("vLongitude", getIntent().getStringExtra("bookingLongitude"));
            parameters.put("iParkingSpaceId", getIntent().getStringExtra("parkingSpacesId"));
            parameters.put("iParkingVehicleSizeId", getIntent().getStringExtra("iParkingVehicleSizeId"));

            ApiHandler.execute(this, parameters, true, false, generalFunc, this::setResponse);

        } else if (getIntent().hasExtra("responseMap")) {
            isCancel = getIntent().hasExtra("isCancel");
            Bundle bundle = this.getIntent().getExtras();
            if (bundle != null) {
                setResponseMap((HashMap<String, String>) bundle.getSerializable("responseMap"));
            }
        }
    }

    private void setResponseMap(HashMap<String, String> responseMap) {
        JSONArray FareDetailsArrNewObj = null;
        FareDetailsArrNewObj = generalFunc.getJsonArray(responseMap.get("PriceInfo"));

        if (isCancel) {
            binding.llCarDetails.setVisibility(View.VISIBLE);
            binding.llAddCarDetails.setVisibility(View.GONE);
            binding.llPaymentType.setVisibility(View.GONE);
            binding.canceltxt.setVisibility(View.VISIBLE);
            binding.editCar.setVisibility(View.GONE);
            binding.editDriver.setVisibility(View.GONE);
            binding.llPaymentMode.setVisibility(View.VISIBLE);
            binding.paymentModeHTxt.setVisibility(View.VISIBLE);
            binding.btnPayAndReserve.setText(generalFunc.retrieveLangLBl("", "LBL_NAVIGATE"));
            if (responseMap.get("ShowCancelBtn").equalsIgnoreCase("no")) {
                binding.llPaymentType.setVisibility(View.GONE);
            } else {
                binding.llPaymentType.setVisibility(View.VISIBLE);
            }
            if (responseMap.get("ShowRating").equalsIgnoreCase("yes")) {
                binding.ratingHTxt.setVisibility(View.VISIBLE);
                binding.ratingHTxt.setVisibility(View.VISIBLE);
            } else {
                binding.ratingLayout.setVisibility(View.GONE);
                binding.ratingLayout.setVisibility(View.GONE);
            }

            switch (Objects.requireNonNull(responseMap.get("status"))) {
                case "Cancelled" -> {
                    binding.cancelReasonTxt.setText(responseMap.get("CancelReason"));
                    binding.btnCash.setVisibility(View.GONE);
                    binding.cancelledView.setVisibility(View.VISIBLE);
                }
                case "Completed" -> {
                    binding.btnType2.setVisibility(View.GONE);
                    binding.cancelledView.setVisibility(View.GONE);
                }
                case "Upcoming" -> {
                    binding.llPaymentType.setVisibility(View.GONE);
                    binding.cancelledView.setVisibility(View.GONE);
                }
                case "InProgress" -> {
                    binding.llPaymentType.setVisibility(View.VISIBLE);
                    binding.cancelledView.setVisibility(View.GONE);
                }
            }
            new LoadImageGlide.builder(this, LoadImageGlide.bind(responseMap.get("PaymentModeImg")), binding.cashImg).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            binding.paymentModeTxt.setText(responseMap.get("PaymentMode"));
            JSONObject obj_Data = generalFunc.getJsonObject(responseMap.get("CarDetails"));

            try {
                new LoadImageGlide.builder(this, LoadImageGlide.bind(obj_Data.getString("vImage")), binding.carImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
                binding.carName.setText(obj_Data.getString("vModel"));
                binding.carModel.setText(obj_Data.getString("vMake") + " " + obj_Data.getString("vModel"));
                binding.carNumber.setText(obj_Data.getString("vCarNumberPlate"));
                JSONObject user_Data = generalFunc.getJsonObject(responseMap.get("tUserDetails"));
                binding.userName.setText(user_Data.getString("vUserName"));
                binding.userNumber.setText(user_Data.getString("vUserPhone"));
            } catch (JSONException e) {
                throw new RuntimeException(e);
            }


        } else {
            binding.btnCash.setVisibility(View.VISIBLE);
            binding.llPaymentType.setVisibility(View.VISIBLE);
            binding.canceltxt.setVisibility(View.GONE);
            binding.editCar.setVisibility(View.VISIBLE);
            binding.editDriver.setVisibility(View.VISIBLE);
            binding.llPaymentMode.setVisibility(View.GONE);
            binding.paymentModeHTxt.setVisibility(View.GONE);
            binding.btnPayAndReserve.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PAY_RESERVE_BTN_TXT"));
        }

        addFareDetailLayout(FareDetailsArrNewObj);
        binding.tAddress.setText(responseMap.get("tAddress"));
        binding.parkingFrom.setText(responseMap.get("ParkingFromTitle"));
        binding.parkingUntil.setText(responseMap.get("ParkingToTitle"));
        binding.fromDate.setText(responseMap.get("ParkingFromDateTime"));
        binding.untilDate.setText(responseMap.get("ParkingToDateTime"));
        binding.totalDuration.setText(responseMap.get("Duration"));
        binding.totalDurationHTxt.setText(responseMap.get("DurationSubText"));

        binding.priceInfoHtTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PRICE_INFORMATION_TITLE"));
        binding.carDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_CAR_DETAILS"));
        binding.driverDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_DETAILS_TITLE"));
    }


    private void setResponse(String responseStr) {
        if (responseStr != null && !responseStr.equalsIgnoreCase("")) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStr)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseStr);
                vehiclesData.clear();
                JSONArray FareDetailsArrNewObj = null;
                addToClickHandler(binding.editCar);
                addToClickHandler(binding.editDriver);
                addToClickHandler(binding.llAddCarDetails);


                JSONObject obj_data = JSONUtils.toJsonObj(responseStr);
                JSONObject obj_msg;

                binding.btnCash.setVisibility(View.VISIBLE);
                binding.llPaymentType.setVisibility(View.VISIBLE);
                binding.canceltxt.setVisibility(View.GONE);
                binding.editCar.setVisibility(View.VISIBLE);
                binding.editDriver.setVisibility(View.VISIBLE);
                binding.llPaymentMode.setVisibility(View.GONE);
                binding.paymentModeHTxt.setVisibility(View.GONE);
                binding.btnPayAndReserve.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PAY_RESERVE_BTN_TXT"));
                FareDetailsArrNewObj = generalFunc.getJsonArray("PriceInfo", message);
                obj_msg = JSONUtils.getJSONObj(Utils.message_str, obj_data);
                JSONArray vehiclesArray;
                vehiclesArray = generalFunc.getJsonArray("ParkingUserVehicles", message);
                if (vehiclesArray.length() > 0) {
                    binding.carNotFoundTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_CAR"));
                    binding.carNotFoundSubTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SELECT_ADD_VEHICLE_SELECTED_SIZE_TXT"));
                    isVehicleList = true;
                    MyUtils.createArrayListJSONArray(generalFunc, vehiclesData, vehiclesArray);
                    for (int i = 0; i < vehiclesData.size(); i++) {
                        HashMap<String, String> data = vehiclesData.get(i);
                        if (data.get("isSelected").equalsIgnoreCase("yes")) {
                            binding.llCarDetails.setVisibility(View.VISIBLE);
                            binding.llAddCarDetails.setVisibility(View.GONE);
                            new LoadImageGlide.builder(this, LoadImageGlide.bind(data.get("vImage")), binding.carImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
                            binding.carName.setText(data.get("vModel"));
                            binding.carModel.setText(data.get("VehicleMakeModel"));
                            binding.carNumber.setText(data.get("vCarNumberPlate"));
                            vehicleId = data.get("iParkingVehicleId");
                            vehicleSizeId = data.get("iParkingVehicleSizeId");
                            break;
                        } else {
                            binding.llCarDetails.setVisibility(View.GONE);
                            binding.llAddCarDetails.setVisibility(View.VISIBLE);
                        }
                    }
                } else {
                    binding.carNotFoundTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_CAR_DETAILS_NOT_FOUND_TXT"));
                    binding.carNotFoundSubTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_NO_USER_VEHICLES_FOUND"));
                    isVehicleList = false;
                    binding.llCarDetails.setVisibility(View.GONE);
                    binding.llAddCarDetails.setVisibility(View.VISIBLE);
                }
                String userProfileJson = "";
                userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                binding.userName.setText(generalFunc.getJsonValue("vName", userProfileJson) + " " + generalFunc.getJsonValue("vLastName", userProfileJson));
                binding.userNumber.setText(generalFunc.getJsonValue("vPhone", userProfileJson));

                addFareDetailLayout(FareDetailsArrNewObj);
                binding.tAddress.setText(generalFunc.getJsonValueStr("tAddress", obj_msg));
                binding.parkingFrom.setText(generalFunc.getJsonValueStr("ParkingFromTitle", obj_msg));
                binding.parkingUntil.setText(generalFunc.getJsonValueStr("ParkingToTitle", obj_msg));
                binding.fromDate.setText(generalFunc.getJsonValueStr("ParkingFromDateTime", obj_msg));
                binding.untilDate.setText(generalFunc.getJsonValueStr("ParkingToDateTime", obj_msg));
                binding.totalDuration.setText(generalFunc.getJsonValueStr("Duration", obj_msg));
                binding.totalDurationHTxt.setText(generalFunc.getJsonValueStr("DurationSubText", obj_msg));

                binding.priceInfoHtTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PRICE_INFORMATION_TITLE"));
                binding.carDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_CAR_DETAILS"));
                binding.driverDetailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DRIVER_DETAILS_TITLE"));
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseStr)));
            }
        } else {
            generalFunc.showError();
        }

    }

    private void addFareDetailLayout(JSONArray jobjArray) {
        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String rName = jobject.names().getString(0);
                String rValue = jobject.get(rName).toString();
                binding.priceInfoContainer.addView(MyUtils.addFareDetailRow(this, generalFunc, rName, rValue, (jobjArray.length() - 1) == i));
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.editCar.getId()) {

            Bundle bn = new Bundle();
            bn.putSerializable("vehicleSizes", getIntent().getSerializableExtra("vehicleSizes"));
            bn.putString("vehicleId", vehicleId);
            bn.putString("iParkingVehicleSizeId", getIntent().getStringExtra("iParkingVehicleSizeId"));
            Intent intent = new Intent(this, ParkingVehicleListActivity.class);
            intent.putExtras(bn);
            launchActivity.launch(intent);

        } else if (i == binding.editDriver.getId()) {
            Bundle bn = new Bundle();
            bn.putString("detailType", "driver");
            bn.putString("vName", binding.userName.getText().toString());
            bn.putString("vPhone", binding.userNumber.getText().toString());
            Intent intent = new Intent(this, AddParkingCarOrDriverActivity.class);
            intent.putExtras(bn);
            launchActivity.launch(intent);
        } else if (i == binding.llAddCarDetails.getId()) {
            if (isVehicleList) {
                Bundle bn = new Bundle();
                bn.putSerializable("vehicleSizes", getIntent().getSerializableExtra("vehicleSizes"));
                bn.putString("iParkingVehicleSizeId", getIntent().getStringExtra("iParkingVehicleSizeId"));
                Intent intent = new Intent(this, ParkingVehicleListActivity.class);
                intent.putExtras(bn);
                launchActivity.launch(intent);

            } else {
                Bundle bn = new Bundle();
                bn.putString("detailType", "car");
                bn.putSerializable("vehicleSizes", getIntent().getSerializableExtra("vehicleSizes"));
                new ActUtils(this).startActWithData(AddParkingCarOrDriverActivity.class, bn);
            }
        } else if (i == binding.btnPayAndReserve.getId()) {
            if (isCancel) {
                openNavigationDialog(false);
            } else {
                reserveSpace();
            }
        } else if (i == binding.btnType2.getId()) {
            if (isCancel) {
                showDeclineReasonsAlert();
            } else {
//                reserveSpace();
            }
        } else if (i == binding.rateBtn.getId()) {
            giveRating();
        }
    }

    private void giveRating() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "SubmitRatingForParking");
        parameters.put("iParkingBookingId", bookingId);
        parameters.put("iParkingSpaceId", parkingSpaceId);
        parameters.put("Feedback", binding.EtReview.getText().toString());
        parameters.put("Rating", ratingValue);
        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    //Do Something
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void openNavigationDialog(boolean isDirection) {
        MyCommonDialog.navigationDialog(this, generalFunc,
                () -> {
                    try {
                        if (isDirection) {
                            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse("google.navigation:q=" + Double.parseDouble(vLatitude) + "," + Double.parseDouble(vLongitude)));
                            startActivity(intent);
                        } else {
                            String url_view = "http://maps.google.com/maps?q=loc:" + Double.parseDouble(vLatitude) + "," + Double.parseDouble(vLongitude);
                            (new ActUtils(this)).openURL(url_view, "com.google.android.apps.maps", "com.google.android.maps.MapsActivity");
                        }
                    } catch (Exception e) {
                        generalFunc.showMessage(binding.cancelReasonTxt, generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
                    }
                }, () -> {
                    try {
                        if (isDirection) {
                            String uri = "https://waze.com/ul?q=" + MyApp.getInstance().currentLocation.getLatitude() + "," + MyApp.getInstance().currentLocation.getLongitude() + "&ll=" + Double.parseDouble(vLatitude) + "," + Double.parseDouble(vLongitude) + "&navigate=yes";
                            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                            startActivity(intent);
                        } else {
                            String uri = "https://waze.com/ul?ll=" + Double.parseDouble(vLatitude) + "," + Double.parseDouble(vLongitude);
                            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                            startActivity(intent);
                        }
                    } catch (Exception e) {
                        generalFunc.showMessage(binding.cancelReasonTxt, generalFunc.retrieveLangLBl("Please install Waze navigation app in your device.", "LBL_INSTALL_WAZE"));
                    }
                });
    }


    private void showDeclineReasonsAlert() {
        if (dialog_declineOrder != null) {
            if (dialog_declineOrder.isShowing()) {
                dialog_declineOrder.dismiss();
            }
            dialog_declineOrder = null;
        }
        selCurrentPosition = -1;

        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(this);
        // builder.setTitle(titleDailog);

        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = (MaterialEditText) dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = (RelativeLayout) dialogView.findViewById(R.id.commentArea);
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

        MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);
        MTextView submitTxt = (MTextView) dialogView.findViewById(R.id.submitTxt);
        MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);
        ImageView cancelImg = (ImageView) dialogView.findViewById(R.id.cancelImg);
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        subTitleTxt.setText(generalFunc.retrieveLangLBl("Cancel Booking", "LBL_CANCEL_BOOKING"));

        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));
        MTextView declinereasonBox = (MTextView) dialogView.findViewById(R.id.declinereasonBox);
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
            cancelBooking(sub_list.get(selCurrentPosition).get("iCancelReasonId"), reasonBox.getText().toString().trim());
            dialog_declineOrder.dismiss();
        });
        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(this);
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });

        cancelImg.setOnClickListener(v -> {
            Utils.hideKeyboard(this);
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });

        declinereasonBox.setOnClickListener(v -> {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "GetCancelReasons");
            parameters.put("iMemberId", generalFunc.getMemberId());
            parameters.put("eUserType", Utils.app_type);
            ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
                sub_list.clear();
                if (responseString != null && !responseString.equalsIgnoreCase("")) {
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
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                } else {
                    generalFunc.showError();
                }
            });
        });
        dialog_declineOrder = builder.create();
        dialog_declineOrder.setCancelable(false);
        dialog_declineOrder.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(this, R.drawable.all_roundcurve_card));
        dialog_declineOrder.show();
    }

    private void cancelBooking(String iCancelReasonId, String reason) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CancelParkingSpaceBooking");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.userType);
        parameters.put("iParkingBookingId", bookingId);
        parameters.put("reason", reason);
        parameters.put("iCancelReasonId", iCancelReasonId);
        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showGeneralMessage("", generalFunc.getJsonValue("message", responseString), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), btn_id -> MyApp.getInstance().restartWithGetDataApp());
                    MyApp.getInstance().restartWithGetDataApp();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void reserveSpace() {
        if (binding.llAddCarDetails.getVisibility() == View.GONE) {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "ReserveParking");
            parameters.put("iParkingSpaceId", getIntent().getStringExtra("parkingSpacesId"));
            parameters.put("ArrivalDate", getIntent().getStringExtra("ArrivalDate"));
            parameters.put("iDurationId", getIntent().getStringExtra("duration"));
            parameters.put("iParkingVehicleId", vehicleId);
            parameters.put("vUserName", binding.userName.getText().toString());
            parameters.put("vUserPhone", binding.userNumber.getText().toString());

            ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
                if (responseString != null && !responseString.equalsIgnoreCase("")) {
                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                        CustomDialog customDialog = new CustomDialog(this, generalFunc);
                        customDialog.setDetails(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), generalFunc.retrieveLangLBl("", "LBL_VIEW_BOOKINGS"), generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), false, R.drawable.ic_correct_2, false, 1, true);
                        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
                        customDialog.setRoundedViewBorderColor(R.color.white);
                        customDialog.setImgStrokWidth(15);
                        customDialog.setBtnRadius(10);
                        customDialog.setIconTintColor(R.color.white);
                        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
                        customDialog.setPositiveBtnTextColor(R.color.white);
                        customDialog.createDialog();
                        customDialog.setPositiveButtonClick(() -> {
                            Bundle bn = new Bundle();
                            bn.putBoolean("isRestartApp", true);
                            bn.putString("isBooking", "yes");
                            new ActUtils(this).startActWithData(ParkingPublishAndBooking.class, bn);
                        });
                        customDialog.setNegativeButtonClick(() -> MyApp.getInstance().restartWithGetDataApp());
                        customDialog.show();
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                } else {
                    generalFunc.showError();
                }
            });
        } else {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PARKING_ADD_CAR_DETAILS_HINT_TXT"), buttonId -> {

            });
        }
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        Intent data = result.getData();
        if (result.getResultCode() == Activity.RESULT_OK && data != null) {
            if (data.hasExtra("detailType") && data.getStringExtra("detailType").equalsIgnoreCase("car")) {
                if (data.hasExtra("vehicleList")) {
                    ArrayList<HashMap<String, String>> vehicles = (ArrayList<HashMap<String, String>>) data.getSerializableExtra("vehicleList");
                    if (vehicles.size() > 0) {
                        setVehicleData(data.getSerializableExtra("vehicleList"));
                    }
                } else {
                    binding.llCarDetails.setVisibility(View.VISIBLE);
                    binding.llAddCarDetails.setVisibility(View.GONE);
                    Bundle bundle = data.getExtras();
                    HashMap<String, String> mapData = (HashMap<String, String>) bundle.getSerializable("vehicleData");
                    new LoadImageGlide.builder(this, LoadImageGlide.bind(mapData.get("vImage")), binding.carImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
                    binding.carName.setText(mapData.get("vModel"));
                    binding.carModel.setText(mapData.get("VehicleMakeModel"));
                    binding.carNumber.setText(mapData.get("vCarNumberPlate"));
                    vehicleId = mapData.get("iParkingVehicleId");
                    vehicleSizeId = mapData.get("iParkingVehicleSizeId");
                }

            } else if (data.hasExtra("detailType") && data.getStringExtra("detailType").equalsIgnoreCase("driver")) {
                binding.userName.setText(data.getStringExtra("vName"));
                binding.userNumber.setText(data.getStringExtra("vPhone"));
            }
        }
    });

    private void setVehicleData(Serializable vehicleList) {
        ArrayList<HashMap<String, String>> vehicles = (ArrayList<HashMap<String, String>>) vehicleList;
        if (vehicles.size() > 0) {
            isVehicleList = true;
            for (int i = 0; i < vehicles.size(); i++) {
                HashMap<String, String> data = vehicles.get(i);
                if (data.get("isSelected").equalsIgnoreCase("yes")) {
                    new LoadImageGlide.builder(this, LoadImageGlide.bind(data.get("vImage")), binding.carImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
                    binding.carName.setText(data.get("vModel"));
                    binding.carModel.setText(data.get("VehicleMakeModel"));
                    binding.carNumber.setText(data.get("vCarNumberPlate"));
                    vehicleId = data.get("iParkingVehicleId");
                    vehicleSizeId = data.get("iParkingVehicleSizeId");
                    binding.llCarDetails.setVisibility(View.VISIBLE);
                    binding.llAddCarDetails.setVisibility(View.GONE);
                    break;
                } else {
                    binding.llCarDetails.setVisibility(View.GONE);
                    binding.llAddCarDetails.setVisibility(View.VISIBLE);
                }
            }
        } else {
            isVehicleList = false;
            binding.llCarDetails.setVisibility(View.GONE);
            binding.llAddCarDetails.setVisibility(View.VISIBLE);
        }

    }
}