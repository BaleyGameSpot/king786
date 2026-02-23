package com.act.rideSharingPro;

import android.annotation.SuppressLint;
import android.location.Location;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.MyWalletActivity;
import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideBookingRequestBinding;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.Polyline;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.LoadImage;
import com.utils.MapUtils;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class RideBookingRequestedActivity extends ParentActivity implements GeoMapLoader.OnMapReadyCallback {

    private ActivityRideBookingRequestBinding binding;
    private HashMap<String, String> myRideDataHashMap;
    private AlertDialog dialogRideDecline;
    private int selCurrentPosition = -1;

    private GeoMapLoader.GeoMap geoMap;
    private Marker startMarker, endMarker;
    private Polyline route_polyLine;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_booking_request);

        myRideDataHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("myRideDataHashMap");
        if (myRideDataHashMap == null) {
            return;
        }

        initialization();
        setData();

        (new GeoMapLoader(this, binding.mapBookReqContainer.getId())).bindMap(this);
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_BOOKING_REQUEST"));

        manageVectorImage(binding.viewGradient, R.drawable.ic_gradient_gray, R.drawable.ic_gradient_gray_compat);

        /////////////
        addToClickHandler(binding.declineBtn);
        addToClickHandler(binding.acceptBtn);
    }

    @SuppressLint("SetTextI18n")
    private void setData() {
        binding.driverNameTxt.setText(myRideDataHashMap.get("DriverName"));

        String driverImage = myRideDataHashMap.get("DriverImg");
        if (!Utils.checkText(driverImage)) {
            driverImage = "Temp";
        }
        new LoadImage.builder(LoadImage.bind(driverImage), binding.driverProfileImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        //////
        binding.dateVTxt.setText(myRideDataHashMap.get("StartDate"));
        binding.startTimeTxt.setText(myRideDataHashMap.get("StartTime"));
        binding.endTimeTxt.setText(myRideDataHashMap.get("EndTime"));

        binding.startCityTxt.setText(myRideDataHashMap.get("tStartCity"));
        binding.endCityTxt.setText(myRideDataHashMap.get("tEndCity"));

        binding.startAddressTxt.setText(myRideDataHashMap.get("tStartLocation"));
        binding.endAddressTxt.setText(myRideDataHashMap.get("tEndLocation"));

        ////////
        binding.totalHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_TOTAL_PRICE"));
        binding.noOfPassengerText.setText(myRideDataHashMap.get("NoOfSeatTxt"));
        binding.fPriceTxt.setText(myRideDataHashMap.get("fPrice"));

        binding.declineBtn.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT"));
        binding.acceptBtn.setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT"));
    }

    private void setBookingsStatus(String status, String iCancelReasonId, String reason) {
        HashMap<String, String> parameters = new HashMap<>();

        parameters.put("type", "UpdateBookingsStatus");
        parameters.put("eStatus", status);

        parameters.put("iPublishedRideId", myRideDataHashMap.get("iPublishedRideId"));
        parameters.put("iBookingId", myRideDataHashMap.get("iBookingId"));

        if (status.equalsIgnoreCase("Declined")) {
            parameters.put("iCancelReasonId", iCancelReasonId);
            parameters.put("tCancelReason", reason);
        }

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (status.equalsIgnoreCase("Approved")) {
                        CustomDialog customDialog = new CustomDialog(this, generalFunc);
                        customDialog.setDetails(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_BOOKING_APPROVED_TXT"),
                                myRideDataHashMap.get("DriverName") + " " + generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_TRAVEL_WITH_YOU_TXT"),
                                generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_SEE_MY_RIDE_TXT"),
                                "",
                                false, R.drawable.ic_correct_2, false, 2, true);
                        customDialog.setRoundedViewBackgroundColor(R.color.appThemeColor_1);
                        customDialog.setRoundedViewBorderColor(R.color.white);
                        customDialog.setImgStrokWidth(15);
                        customDialog.setBtnRadius(10);
                        customDialog.setIconTintColor(R.color.white);
                        customDialog.setPositiveBtnBackColor(R.color.appThemeColor_2);
                        customDialog.setPositiveBtnTextColor(R.color.white);
                        customDialog.createDialog();
                        customDialog.setPositiveButtonClick(() -> {
                            if (ServiceModule.OnlyRideSharingPro && getIntent().hasExtra("isFromRideShareRideFragment") && getIntent().getBooleanExtra("isFromRideShareRideFragment", false)) {
                                new ActUtils(this).setOkResult();
                            } else {
                                Bundle bn = new Bundle();
                                bn.putBoolean("isRestartApp", true);
                                new ActUtils(this).startActWithData(RideMyList.class, bn);
                            }
                            finish();
                        });
                        customDialog.show();
                    } else {
                        finish();
                    }
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

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.declineBtn.getId()) {
            showRideDeclineReasonsAlert();
        } else if (i == binding.acceptBtn.getId()) {
            setBookingsStatus("Approved", "", "");
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
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        MTextView subTitleTxt = dialogView.findViewById(R.id.subTitleTxt);
        ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);

        subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DECLINE_RIDE"));

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
            setBookingsStatus("Declined", sub_list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox));
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
            parameters.put("PublishedRideDecline", "Yes");

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
        dialogRideDecline.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(this, R.drawable.all_roundcurve_card));
        dialogRideDecline.show();
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {
        this.geoMap = geoMap;
        geoMap.setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });
        setLocationMarkers(true, myRideDataHashMap.get("tStartLat"), myRideDataHashMap.get("tStartLong"));
        setLocationMarkers(false, myRideDataHashMap.get("tEndLat"), myRideDataHashMap.get("tEndLong"));
    }

    private void setLocationMarkers(boolean isStartLocation, String latitude, String longitude) {
        Location resultLocation = new Location("");
        resultLocation.setLatitude(GeneralFunctions.parseDoubleValue(0.0, latitude));
        resultLocation.setLongitude(GeneralFunctions.parseDoubleValue(0.0, longitude));
        LatLng latLng = new LatLng(resultLocation.getLatitude(), resultLocation.getLongitude());
        geoMap.moveCamera(latLng);

        if (isStartLocation) {
            if (startMarker == null) {
                startMarker = geoMap.addMarker(new MarkerOptions().position(latLng).icon(BitmapDescriptorFactory.fromResource(R.drawable.dot)));
            } else {
                startMarker.setIcon(BitmapDescriptorFactory.fromResource(R.drawable.dot));
                startMarker.setPosition(latLng);
            }
        } else {
            if (endMarker == null) {
                endMarker = geoMap.addMarker(new MarkerOptions().position(latLng).icon(BitmapDescriptorFactory.fromResource(R.drawable.dot)));
            } else {
                endMarker.setPosition(latLng);
            }
        }
        findRoute();
    }

    private void findRoute() {

        if (startMarker == null || endMarker == null) {
            return;
        }
        LatLng sLatLng = startMarker.getPosition();
        LatLng eLatLng = endMarker.getPosition();

        AppService.getInstance().executeService(this, new DataProvider.DataProviderBuilder(sLatLng.latitude + "", sLatLng.longitude + "").setDestLatitude(eLatLng.latitude + "").setDestLongitude(eLatLng.longitude + "").setWayPoints(new JSONArray()).build(), AppService.Service.DIRECTION, data -> {
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }

            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                return;
            }

            String responseString = data.get("RESPONSE_DATA").toString();

            if (responseString.equalsIgnoreCase("")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }

            if (!responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {

                JSONArray obj_routes = generalFunc.getJsonArray("routes", responseString);
                if (obj_routes != null && obj_routes.length() > 0) {


                    responseString = data.get("ROUTES").toString();
                    route_polyLine = MapUtils.handleMapAnimation(this, generalFunc, responseString, sLatLng, eLatLng, geoMap, route_polyLine, true, false);

                } else {
                    generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_ERROR_TXT"), generalFunc.retrieveLangLBl("", "LBL_GOOGLE_DIR_NO_ROUTE"));
                }
            } else {

                HashMap<String, Object> data_dict = new HashMap<>();
                data_dict.put("routes", data.get("ROUTES"));
                responseString = data_dict.toString();
                route_polyLine = MapUtils.handleMapAnimation(this, generalFunc, responseString, sLatLng, eLatLng, geoMap, route_polyLine, false, false);
            }
        });
    }
}