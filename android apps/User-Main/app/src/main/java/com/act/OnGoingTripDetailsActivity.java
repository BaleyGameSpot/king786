package com.act;

import static com.utils.MapUtils.createDrawableFromView;
import static com.utils.Utils.APP_TYPE;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.Toolbar;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.OnGoingTripDetailAdapter;
import com.dialogs.OpenListView;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.features.SafetyTools;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.RecurringTask;
import com.general.files.UpdateDirections;
import com.buddyverse.main.R;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.helper.MarkerAnim;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.model.EventInformation;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;
import com.view.editBox.MaterialEditText;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 22-02-2017.
 */
public class OnGoingTripDetailsActivity extends ParentActivity implements GeoMapLoader.OnMapReadyCallback, RecurringTask.OnTaskRunCalled {


    ProgressBar loading_ongoing_trips_detail;
    RelativeLayout loadingArea;
    ErrorView errorView;
    RecyclerView onGoingTripsDetailListRecyclerView;
    ImageView backImgView;
    SelectableRoundedImageView user_img;
    MTextView userNameTxt, userAddressTxt, subTitleTxt, titleTxt;
    SimpleRatingBar ratingBar;
    OnGoingTripDetailAdapter onGoingTripDetailAdapter;
    ArrayList<HashMap<String, String>> list;
    HashMap<String, String> tempMap;
    String server_time = "";

    MTextView progressHinttext;
    String driverStatus = "";

    LatLng driverLocation;
    Marker driverMarker;
    GeoMapLoader.GeoMap geoMap;
    LinearLayout googlemaparea;
    boolean isarrived = false;
    boolean isarrivedpopup = false;

    MTextView timeTxt;
    String eType = "";
    UpdateDirections updateDirections;
    Location destLoc;
    View marker_view = null;
    SelectableRoundedImageView providerImgView = null;
    MarkerAnim animDriverMarker;
    boolean ishowdialog = false;

    boolean isLiveTrack = false;

    float defaultMarkerAnimDuration = 1200;
    AlertDialog dialog_declineOrder;

    String vName = "";
    String vImage = "";
    String vRating = "";
    int ADDITIONAL_CHARGES_CODE = 12112;
    public String tripDetailJson;
    public HashMap<String, String> tripDetail = new HashMap<>();
    boolean isBid = false;
    String eVideoCallFacilities = "No";
    private String vChargesDetailData;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.layout_ongoing_trip_details);
        isBid = getIntent().getBooleanExtra("isBid", false);
        Toolbar toolbar = findViewById(R.id.toolbar);
        generalFunc.setOverflowButtonColor(toolbar, getResources().getColor(R.color.white));

        animDriverMarker = new MarkerAnim();

        String PUBNUB_DISABLED = generalFunc.retrieveValue(Utils.PUBNUB_DISABLED_KEY);

        if (PUBNUB_DISABLED.equalsIgnoreCase("NO")) {
            defaultMarkerAnimDuration = 3000;
        }

        init();

        tripDetail = (HashMap<String, String>) getIntent().getSerializableExtra("TripDetail");
        tripDetailJson = generalFunc.getJsonValueStr("TripDetails", obj_userProfile);

        getTripDeliveryLocations();

        (new GeoMapLoader(this, R.id.mapFragmentContainer)).bindMap(this);

        if (isBid) {
            progressHinttext.setText(generalFunc.retrieveLangLBl("Task PROGRESS", "LBL_TASK_PROGRESS"));
        } else if (!generalFunc.getJsonValue("eType", tripDetailJson).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            progressHinttext.setText(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_PROGRESS_HINT"));
        } else {
            progressHinttext.setText(generalFunc.retrieveLangLBl("Delivery Status", "LBL_DELIVERY_STATUS_TXT"));
        }

        if (getIntent().hasExtra("showChargesScreen")) {
            redirectToDetailCharges("");
        }

        Toolbar mToolbar = (Toolbar) findViewById(R.id.toolbar);

        setSupportActionBar(mToolbar);
    }

    public void callUpdateDeirection(Location driverlocation) {
        if (destLoc == null) {
            return;

        }
        if (updateDirections == null) {
            updateDirections = new UpdateDirections(getActContext(), null, driverlocation, destLoc);
            updateDirections.scheduleDirectionUpdate();
        } else {
            updateDirections.changeUserLocation(driverlocation);
        }

    }


    public void setData() {
        if (tripDetail != null) {

            if (tripDetail.get("eType") != null && tripDetail.get("eType").equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                vName = generalFunc.getJsonValue("vName", tripDetailJson);
                vRating = generalFunc.getJsonValue("MemberRating", tripDetailJson);
                vImage = generalFunc.getJsonValue("vUserImage", tripDetailJson);
            } else {
                vName = generalFunc.getJsonValue("driverName", tripDetailJson);
                vRating = generalFunc.getJsonValue("driverRating", tripDetailJson);
                vImage = generalFunc.getJsonValue("driverImage", tripDetailJson);
            }
        } else {
            vName = generalFunc.getJsonValue("driverName", tripDetailJson);
            vRating = generalFunc.getJsonValue("driverRating", tripDetailJson);
            vImage = generalFunc.getJsonValue("driverImage", tripDetailJson);
        }


        setDriverDetail();


        setLables();

    }

    public void subscribeToDriverLocChannel() {
        if (generalFunc.getJsonValue("eFareType", tripDetailJson).equals(Utils.CabFaretypeRegular) || !isarrived) {
            ArrayList<String> channelName = new ArrayList<>();
            channelName.add(Utils.pubNub_Update_Loc_Channel_Prefix + generalFunc.getJsonValue("iDriverId", tripDetailJson));
            AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).build(), AppService.Event.SUBSCRIBE);

        }
    }

    public void unSubscribeToDriverLocChannel() {

        ArrayList<String> channelName = new ArrayList<>();
        channelName.add(Utils.pubNub_Update_Loc_Channel_Prefix + generalFunc.getJsonValue("iDriverId", tripDetailJson));
        AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).build(), AppService.Event.UNSUBSCRIBE);


        if (updateDirections != null) {
            updateDirections.releaseTask();
            updateDirections = null;
        }

    }

    @Override
    protected void onResume() {
        super.onResume();

        subscribeToDriverLocChannel();
        if (updateDirections != null) {
            updateDirections.scheduleDirectionUpdate();
        }


    }

    @Override
    protected void onPause() {
        super.onPause();


        unSubscribeToDriverLocChannel();

    }

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_MENU) {

            // perform your desired action here

            // return 'true' to prevent further propagation of the key event
            return true;
        }

        // let the system handle all other key events
        return super.onKeyDown(keyCode, event);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater menuInflater = getMenuInflater();
        menuInflater.inflate(R.menu.my_ongoing_activity, menu);
        setLablesAsPerCurrentFrag(menu);

        return true;
    }

    @Override
    public void onOptionsMenuClosed(Menu menu) {

        setLablesAsPerCurrentFrag(menu);

        super.onOptionsMenuClosed(menu);
    }

    @Override
    public boolean onMenuOpened(int featureId, Menu menu) {
        setLablesAsPerCurrentFrag(menu);
        return super.onMenuOpened(featureId, menu);
    }

    @Override
    public boolean onPrepareOptionsMenu(Menu menu) {

        setLablesAsPerCurrentFrag(menu);

        return super.onPrepareOptionsMenu(menu);
    }

    public void setLablesAsPerCurrentFrag(Menu menu) {
        if (menu != null) {

            if (driverStatus == null) {
                if (getIntent().hasExtra("driverStatus")) {
                    driverStatus = getIntent().getStringExtra("driverStatus");
                }
            }

            if (Utils.checkText(driverStatus) && !driverStatus.equals("On Going Trip") && !driverStatus.equals("finished") && !driverStatus.equals("NONE") && !driverStatus.equals("Cancelled") && !driverStatus.equals("Canceled")) {
                menu.findItem(R.id.menu_cancel_trip).setVisible(true);
                menu.findItem(R.id.menu_cancel_trip).setTitle(generalFunc.retrieveLangLBl("Cancel Job", "LBL_CANCEL_BOOKING"));
            } else {
                menu.findItem(R.id.menu_cancel_trip).setVisible(false);

            }


            /*if (generalFunc.getJsonValue("eType", tripDetailJson).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                menu.findItem(R.id.menu_cancel_trip).setVisible(false);
            }*/

            if (generalFunc.getJsonValue("moreServices", tripDetailJson) != null && generalFunc.getJsonValue("moreServices", tripDetailJson).equalsIgnoreCase("Yes")) {
                menu.findItem(R.id.menu_service).setVisible(true);
                menu.findItem(R.id.menu_service).setTitle(generalFunc.retrieveLangLBl("", "LBL_TITLE_REQUESTED_SERVICES"));
            } else {
                menu.findItem(R.id.menu_service).setVisible(false);
            }

            if (Utils.checkText(vChargesDetailData)) {
                menu.findItem(R.id.menu_service_charges).setVisible(true);
                menu.findItem(R.id.menu_service_charges).setTitle(generalFunc.retrieveLangLBl("", "LBL_VERIFY_ADDITIONAL_CHARGES_TXT"));
            } else {
                menu.findItem(R.id.menu_service_charges).setVisible(false);
            }


            if (driverStatus.equals("Arrived")) {
                isarrived = true;

            }

            if (driverStatus.equals("On Going Trip")) {
                isarrived = true;

            }

            if (generalFunc.getJsonValue("eType", tripDetailJson).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                menu.findItem(R.id.menu_track).setVisible(false);
            }


            if (onGoingTripsDetailListRecyclerView.getVisibility() == View.VISIBLE) {

                if (generalFunc.getJsonValue("eServiceLocation", tripDetailJson).equalsIgnoreCase("Driver")) {

                    menu.findItem(R.id.menu_track).setTitle(generalFunc.retrieveLangLBl("Live Track", "LBL_NAVIGATE_TO_PROVIDER"));
                } else {
                    menu.findItem(R.id.menu_track).setTitle(generalFunc.retrieveLangLBl("Live Track", "LBL_LIVE_TRACK_TXT"));
                }
            } else {
                menu.findItem(R.id.menu_track).setTitle(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_JOB_PROGRESS"));

                if (isBid) {
                    menu.findItem(R.id.menu_track).setTitle(generalFunc.retrieveLangLBl("Task PROGRESS", "LBL_TASK_PROGRESS"));

                }

            }


            menu.findItem(R.id.menu_call).setTitle(generalFunc.retrieveLangLBl("Call", "LBL_CALL_ACTIVE_TRIP"));
            if (eVideoCallFacilities != null && eVideoCallFacilities.equalsIgnoreCase("Yes")) {
                menu.findItem(R.id.menu_call).setVisible(false);
                menu.findItem(R.id.menu_sos).setVisible(false);
            }
            menu.findItem(R.id.menu_message).setTitle(generalFunc.retrieveLangLBl("Message", "LBL_MESSAGE_ACTIVE_TRIP"));

            if (!generalFunc.getJsonValue("eFareType", tripDetailJson).equals(Utils.CabFaretypeRegular)) {
                if (isarrived) {
                    menu.findItem(R.id.menu_track).setTitle(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_PROGRESS_HINT")).setVisible(false);
                    onGoingTripsDetailListRecyclerView.setVisibility(View.VISIBLE);
                    progressHinttext.setText(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_PROGRESS_HINT"));

                    googlemaparea.setVisibility(View.GONE);
                    timeTxt.setVisibility(View.GONE);
                    isLiveTrack = false;
                    if (isBid) {
                        progressHinttext.setText(generalFunc.retrieveLangLBl("Task PROGRESS", "LBL_TASK_PROGRESS"));
                    }

                }
            }


            if (isBid) {
                menu.findItem(R.id.menu_cancel_trip).setVisible(false);
                menu.findItem(R.id.menu_sos).setVisible(false);
                menu.findItem(R.id.menu_track).setVisible(false);
                menu.findItem(R.id.menu_service).setVisible(true);
                menu.findItem(R.id.menu_service).setTitle(generalFunc.retrieveLangLBl("Requested Biddings", "LBL_REQUESTED_BIDDING"));
            }

            menu.findItem(R.id.menu_sos).setTitle(generalFunc.retrieveLangLBl("Emergency or SOS", "LBL_EMERGENCY_SOS_TXT"));

            Utils.setMenuTextColor(menu.findItem(R.id.menu_cancel_trip), getResources().getColor(R.color.black));
            Utils.setMenuTextColor(menu.findItem(R.id.menu_sos), getResources().getColor(R.color.black));
            Utils.setMenuTextColor(menu.findItem(R.id.menu_track), getResources().getColor(R.color.black));
            Utils.setMenuTextColor(menu.findItem(R.id.menu_call), getResources().getColor(R.color.black));
            Utils.setMenuTextColor(menu.findItem(R.id.menu_message), getResources().getColor(R.color.black));
            Utils.setMenuTextColor(menu.findItem(R.id.menu_service), getResources().getColor(R.color.black));
            Utils.setMenuTextColor(menu.findItem(R.id.menu_service_charges), getResources().getColor(R.color.black));

        }
    }

    String titleDailog = "";
    int selCurrentPosition = -1;

    public void showDeclineReasonsAlert() {
        if (dialog_declineOrder != null) {
            if (dialog_declineOrder.isShowing()) {
                dialog_declineOrder.dismiss();
            }
            dialog_declineOrder = null;
        }
        selCurrentPosition = -1;
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
//        builder.setTitle(generalFunc.retrieveLangLBl("", "LBL_CANCEL_BOOKING"));

        if (generalFunc.getJsonValue("eType", tripDetailJson).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            titleDailog = (generalFunc.retrieveLangLBl("", "LBL_CANCEL_BOOKING"));
        } else {
            titleDailog = (generalFunc.retrieveLangLBl("", "LBL_CANCEL_DELIVERY"));
        }

        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);
        MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);
        MTextView submitTxt = (MTextView) dialogView.findViewById(R.id.submitTxt);
        MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        subTitleTxt.setText(titleDailog);
        MaterialEditText reasonBox = (MaterialEditText) dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = (RelativeLayout) dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setVisibility(View.GONE);
        ImageView cancelImg = (ImageView) dialogView.findViewById(R.id.cancelImg);
        cancelImg.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });

        reasonBox.setHideUnderline(true);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            reasonBox.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));

        ArrayList<HashMap<String, String>> sub_list = new ArrayList<>();

        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));

        MTextView declinereasonBox = (MTextView) dialogView.findViewById(R.id.declinereasonBox);
        declinereasonBox.setText("-- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_CANCEL_REASON") + " --");
        submitTxt.setClickable(false);
        submitTxt.setTextColor(getResources().getColor(R.color.gray_holo_light));
        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });

        // AppCompatSpinner spinner = (AppCompatSpinner) dialogView.findViewById(R.id.declineReasonsSpinner);
           /* CustSpinnerAdapter adapter = new CustSpinnerAdapter(getActContext(), list);
            spinner.setAdapter(adapter);


            spinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                @Override
                public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {

                    if (spinner.getSelectedItemPosition() == (list.size() - 1)) {
                        reasonBox.setVisibility(View.VISIBLE);
                        commentArea.setVisibility(View.VISIBLE);
                        //dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(true);
                      ////  dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setClickable(true);
                      //  dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getActContext().getResources().getColor(R.color.black));
                    } else if (spinner.getSelectedItemPosition() == 0) {
                        //  dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(false);
                       // dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setClickable(false);
                       // dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getActContext().getResources().getColor(R.color.gray));
                        reasonBox.setVisibility(View.GONE);
                        commentArea.setVisibility(View.GONE);
                    } else {
                        //dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(true);
                      //  dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setClickable(true);
                       // dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getActContext().getResources().getColor(R.color.black));
                        reasonBox.setVisibility(View.GONE);
                        commentArea.setVisibility(View.GONE);
                    }
                }

                @Override
                public void onNothingSelected(AdapterView<?> parent) {

                }
            });*/

        declinereasonBox.setOnClickListener(v -> {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "GetCancelReasons");
            parameters.put("iMemberId", generalFunc.getMemberId());
            parameters.put("eUserType", Utils.app_type);
            parameters.put("eJobType", tripDetail.get("eJobType"));

            parameters.put("iTripId", getIntent().getStringExtra("iTripId"));

            ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
                sub_list.clear();
                if (Utils.checkText(responseString)) {

                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
                        if (arr_msg != null) {

                            for (int i = 0; i < arr_msg.length(); i++) {

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

                            OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_REASON"), sub_list, OpenListView.OpenDirection.CENTER, true, position -> {


                                selCurrentPosition = position;
                                HashMap<String, String> mapData = sub_list.get(position);
                                errorTextView.setVisibility(View.GONE);
                                declinereasonBox.setText(mapData.get("title"));
                                if (selCurrentPosition == (sub_list.size() - 1)) {
                                    reasonBox.setVisibility(View.VISIBLE);
                                    commentArea.setVisibility(View.VISIBLE);
                                } else {
                                    reasonBox.setVisibility(View.GONE);
                                    commentArea.setVisibility(View.GONE);
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


        submitTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());

            if (selCurrentPosition == -1) {
                return;
            }
            if (Utils.checkText(reasonBox) == false && selCurrentPosition == (sub_list.size() - 1)) {
                errorTextView.setVisibility(View.VISIBLE);
                errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                return;
            }
            cancelTrip("No", sub_list.get(selCurrentPosition).get("id"), reasonBox.getText().toString().trim());

            dialog_declineOrder.dismiss();
        });


        dialog_declineOrder = builder.create();
        dialog_declineOrder.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        dialog_declineOrder.show();

        //  dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(false);
          /*  dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setClickable(false);
            dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getActContext().getResources().getColor(R.color.gray));*/

           /* dialog_declineOrder.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {


                    if (spinner.getSelectedItemPosition() == 0) {
                        return;
                    }

                    if (Utils.checkText(reasonBox) == false && spinner.getSelectedItemPosition() == (list.size() - 1)) {
                        reasonBox.setError(generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));
                        return;
                    }

                    // declineOrder(arrListIDs.get(spinner.getSelectedItemPosition()), Utils.getText(reasonBox));
//                    new CancelTripDialog(getActContext(), data_trip, generalFunc, arrListIDs.get(spinner.getSelectedItemPosition()), Utils.getText(reasonBox), isTripStart);

                    cancelTrip("No", list.get(spinner.getSelectedItemPosition()).get("id"), reasonBox.getText().toString().trim());

                    dialog_declineOrder.dismiss();
                }
            });*/
/*
            dialog_declineOrder.getButton(AlertDialog.BUTTON_NEGATIVE).setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    dialog_declineOrder.dismiss();
                }
            });*/
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        int itemId = item.getItemId();
        if (itemId == R.id.menu_cancel_trip) {
            showDeclineReasonsAlert();
            return true;
        } else if (itemId == R.id.menu_sos) {
            if (generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {
                SafetyTools.getInstance().initiate(getActContext(), generalFunc, getIntent().getStringExtra("iTripId"), "");
                SafetyTools.getInstance().safetyToolsDialog(false);
            } else {
                Bundle bn = new Bundle();
                bn.putString("TripId", getIntent().getStringExtra("iTripId"));
                new ActUtils(getActContext()).startActWithData(ConfirmEmergencyTapActivity.class, bn);
            }
            return true;
        } else if (itemId == R.id.menu_track) {
            String eServiceLocation = generalFunc.getJsonValue("eServiceLocation", tripDetailJson);
            if (item.getTitle().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("Live Track", "LBL_LIVE_TRACK_TXT"))) {
                item.setTitle(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_PROGRESS_HINT"));
                onGoingTripsDetailListRecyclerView.setVisibility(View.GONE);

                if (eServiceLocation.equalsIgnoreCase("Driver")) {
                    progressHinttext.setText(generalFunc.retrieveLangLBl("Live Track", "LBL_NAVIGATE_TO_PROVIDER"));
                } else {
                    progressHinttext.setText(generalFunc.retrieveLangLBl("Live Tarck", "LBL_LIVE_TRACK_TXT"));
                }

                googlemaparea.setVisibility(View.VISIBLE);
                if (timeTxt.length() > 0) {
                    timeTxt.setVisibility(View.VISIBLE);
                } else {
                    timeTxt.setVisibility(View.GONE);
                }

                //
                isLiveTrack = true;
                subscribeToDriverLocChannel();

            } else if (item.getTitle().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("Live Track", "LBL_NAVIGATE_TO_PROVIDER"))) {

                try {
                    isLiveTrack = false;
                    if (eServiceLocation.equalsIgnoreCase("Driver")) {
                        item.setTitle(generalFunc.retrieveLangLBl("Live Track", "LBL_NAVIGATE_TO_PROVIDER"));
                    } else {
                        item.setTitle(generalFunc.retrieveLangLBl("Live Tarck", "LBL_LIVE_TRACK_TXT"));
                    }
                    onGoingTripsDetailListRecyclerView.setVisibility(View.VISIBLE);
                    progressHinttext.setText(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_PROGRESS_HINT"));
                    googlemaparea.setVisibility(View.GONE);
                    timeTxt.setVisibility(View.GONE);

                    if (isBid) {
                        progressHinttext.setText(generalFunc.retrieveLangLBl("Task PROGRESS", "LBL_TASK_PROGRESS"));
                    }

                    //

                    String url_view = "http://maps.google.com/maps?daddr=" + generalFunc.getJsonValue("tSaddress", tripDetailJson);
                    (new ActUtils(getActContext())).openURL(url_view, "com.google.android.apps.maps", "com.google.android.maps.MapsActivity");
                } catch (Exception e) {

                }


            } else {
                isLiveTrack = false;
                if (eServiceLocation.equalsIgnoreCase("Driver")) {
                    item.setTitle(generalFunc.retrieveLangLBl("Live Track", "LBL_NAVIGATE_TO_PROVIDER"));
                } else {
                    item.setTitle(generalFunc.retrieveLangLBl("Live Tarck", "LBL_LIVE_TRACK_TXT"));
                }
                onGoingTripsDetailListRecyclerView.setVisibility(View.VISIBLE);
                progressHinttext.setText(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_PROGRESS_HINT"));
                googlemaparea.setVisibility(View.GONE);
                timeTxt.setVisibility(View.GONE);
                unSubscribeToDriverLocChannel();
                if (isBid) {
                    progressHinttext.setText(generalFunc.retrieveLangLBl("Task PROGRESS", "LBL_TASK_PROGRESS"));
                }
            }

            return true;
        } else if (itemId == R.id.menu_call || itemId == R.id.menu_message) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(generalFunc.getJsonValue("iDriverId", tripDetailJson))
                    .setPhoneNumber("+" + generalFunc.getJsonValue("vCode", tripDetailJson) + "" + generalFunc.getJsonValue("driverMobile", tripDetailJson))
                    .setToMemberType(Utils.CALLTODRIVER)
                    .setToMemberName(vName)
                    .setToMemberImage(vImage)
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(isBid ? getIntent().getStringExtra("iBiddingPostId") : generalFunc.getJsonValue("iTripId", tripDetailJson))
                    .setBookingNo(isBid ? generalFunc.getJsonValue("vBiddingPostNo", tripDetailJson) : generalFunc.getJsonValue("vRideNo", tripDetailJson))
                    .setBid(isBid)
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, item.getItemId() == R.id.menu_message ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);
            return true;
        } else if (itemId == R.id.menu_service) {
            Bundle bnService = new Bundle();
            if (isBid) {
                bnService.putString("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));
                bnService.putBoolean("isDetailsView", true);
                new ActUtils(getActContext()).startActWithData(BiddingTaskActivity.class, bnService);
            } else {

                bnService.putString("iTripId", getIntent().getStringExtra("iTripId"));
                bnService.putString("iDriverId", generalFunc.getJsonValue("iDriverId", tripDetailJson));
                new ActUtils(getActContext()).startActWithData(MoreServiceInfoActivity.class, bnService);
            }
            return true;
        } else if (itemId == R.id.menu_service_charges) {
            redirectToDetailCharges("");
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private void redirectToDetailCharges(String verifyPushMsg) {
        Bundle bnService1 = new Bundle();
        bnService1.putString("iTripId", getIntent().getStringExtra("iTripId"));
        bnService1.putString("iDriverId", generalFunc.getJsonValue("iDriverId", tripDetailJson));
        bnService1.putString("eType", getIntent().getStringExtra("eType"));
        bnService1.putSerializable("TripDetail", tripDetail);

        if (Utils.checkText(verifyPushMsg)) {
            tripDetail.put("eApproveRequestSentByDriver", generalFunc.getJsonValue("eApproveRequestSentByDriver", verifyPushMsg));
            bnService1.putString("fMaterialFee", generalFunc.getJsonValue("fMaterialFee", verifyPushMsg));
            bnService1.putString("fMiscFee", generalFunc.getJsonValue("fMiscFee", verifyPushMsg));
            bnService1.putString("fDriverDiscount", generalFunc.getJsonValue("fDriverDiscount", verifyPushMsg));
            bnService1.putString("vConfirmationCode", generalFunc.getJsonValue("vConfirmationCode", verifyPushMsg));
            bnService1.putString("serviceCost", generalFunc.getJsonValue("serviceCost", verifyPushMsg));
            bnService1.putString("totalAmount", generalFunc.getJsonValue("totalAmount", verifyPushMsg));
        } else {
            bnService1.putString("fMaterialFee", generalFunc.getJsonValue("fMaterialFee", vChargesDetailData));
            bnService1.putString("fMiscFee", generalFunc.getJsonValue("fMiscFee", vChargesDetailData));
            bnService1.putString("fDriverDiscount", generalFunc.getJsonValue("fDriverDiscount", vChargesDetailData));
            bnService1.putString("vConfirmationCode", generalFunc.getJsonValue("vConfirmationCode", vChargesDetailData));
            bnService1.putString("serviceCost", generalFunc.getJsonValue("serviceCost", vChargesDetailData));
            bnService1.putString("totalAmount", generalFunc.getJsonValue("totalAmount", vChargesDetailData));

        }
        bnService1.putString("eApproveRequestSentByDriver", tripDetail.get("eApproveRequestSentByDriver"));
        bnService1.putString("CurrencySymbol", tripDetail.get("CurrencySymbol"));

        new ActUtils(getActContext()).startActForResult(AdditionalChargeActivity.class, bnService1, ADDITIONAL_CHARGES_CODE);

    }

    public void cancelTrip(String eConfirmByUser, String iCancelReasonId, String reason) {


        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "cancelTrip");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iDriverId", generalFunc.getJsonValue("iDriverId", tripDetailJson));
        parameters.put("iTripId", getIntent().getStringExtra("iTripId"));
        parameters.put("eConfirmByUser", eConfirmByUser);
        parameters.put("iCancelReasonId", iCancelReasonId);
        parameters.put("Reason", reason);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail == true) {
                    // finish();
                    GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> MyApp.getInstance().refreshView(getActContext(), responseString));

                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message1", responseString)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();

                } else {
                    if (generalFunc.getJsonValue("isCancelChargePopUpShow", responseString).equalsIgnoreCase("Yes")) {
                        if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY) || message.equals("LBL_SERVER_COMM_ERROR")) {
                            MyApp.getInstance().restartWithGetDataApp();
                            return;
                        }
                        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                        generateAlert.setCancelable(false);
                        generateAlert.setBtnClickList(btn_id -> {
                            if (btn_id == 0) {
                                generateAlert.closeAlertBox();

                            } else {
                                generateAlert.closeAlertBox();
                                cancelTrip("Yes", iCancelReasonId, reason);

                            }

                        });
                        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
                        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
                        generateAlert.showAlertBox();

                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }

//                    buildWarningMessage(generalFunc.retrieveLangLBl("", "LBL_REQUEST_FAILED_PROCESS"),
//                            generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), "", false);
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void getTripDeliveryLocations() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        loadingArea.setVisibility(View.VISIBLE);
        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("iCabBookingId", "");
        parameters.put("type", getIntent().getStringExtra("eType").equalsIgnoreCase(Utils.eType_Multi_Delivery) ? "getTripDeliveryDetails" : "getTripDeliveryLocations");
        if (isBid) {
            parameters.put("type", "getTaskLocations");
            parameters.put("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));
        } else {
            parameters.put("iTripId", getIntent().getStringExtra("iTripId"));
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                closeLoader();

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    list = new ArrayList<>();

                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    server_time = generalFunc.getJsonValue("SERVER_TIME", responseString);
                    String driverDetails = generalFunc.getJsonValue("driverDetails", message);

                    destLoc = new Location("Dest");
                    destLoc.setLatitude(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("tStartLat", driverDetails)));
                    destLoc.setLongitude(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("tStartLong", driverDetails)));


                    eType = generalFunc.getJsonValue("eType", driverDetails);
                    eVideoCallFacilities = generalFunc.getJsonValue("isVideoCall", driverDetails);

                    driverStatus = generalFunc.getJsonValue("driverStatus", driverDetails);

                    JSONArray tripLocations = generalFunc.getJsonArray("States", message);

                    if (!driverStatus.equalsIgnoreCase("Active")) {
                        isarrived = true;
                    } else {
                        isarrived = false;
                    }

                    Logger.e("DRIVER_STATUS", "::" + isarrived);

                    list.clear();
                    if (tripLocations != null)
                        for (int i = 0; i < tripLocations.length(); i++) {
                            tempMap = new HashMap<>();

                            JSONObject jobject1 = generalFunc.getJsonObject(tripLocations, i);
                            tempMap.put("status", generalFunc.getJsonValue("type", jobject1.toString()));
                            tempMap.put("iTripId", generalFunc.getJsonValue("text", jobject1.toString()));
                            tempMap.put("value", generalFunc.getJsonValue("timediff", jobject1.toString()));
                            tempMap.put("Booking_LBL", generalFunc.retrieveLangLBl("", "LBL_BOOKING"));
                            tempMap.put("time", generalFunc.getJsonValue("time", jobject1.toString()));
                            tempMap.put("tDisplayDate", generalFunc.getJsonValue("tDisplayDate", jobject1.toString()));
                            tempMap.put("tDisplayTime", generalFunc.getJsonValue("tDisplayTime", jobject1.toString()));
                            tempMap.put("tDisplayDateTime", generalFunc.getJsonValue("tDisplayDateTime", jobject1.toString()));
                            tempMap.put("tDisplayTimeAbbr", generalFunc.getJsonValue("tDisplayTimeAbbr", jobject1.toString()));
                            tempMap.put("eType", generalFunc.getJsonValue("eType", jobject1.toString()));

                            if (tripDetail != null) {
                                tempMap.put("eType", tripDetail.get("eType"));
                                /*Multi Related Details*/
                                if (tripDetail.get("eType").equalsIgnoreCase(Utils.eType_Multi_Delivery) && Utils.checkText(generalFunc.getJsonValue("vDeliveryConfirmCode", jobject1.toString()))) {
                                    tempMap.put("msg", generalFunc.getJsonValue("text", jobject1.toString()) + " " + generalFunc.retrieveLangLBl("Delivery Confirmation Code", "LBL_DELIVERY_CONFIRMATION_CODE_TXT") + " " + generalFunc.getJsonValue("vDeliveryConfirmCode", jobject1.toString()));

                                } else {

                                    tempMap.put("msg", generalFunc.getJsonValue("text", jobject1.toString()));
                                }
                            } else {
                                tempMap.put("msg", generalFunc.getJsonValue("text", jobject1.toString()));
                            }
                            list.add(tempMap);
                        }
                    setView();
                    if (tripDetail != null) {
                        if (tripDetail.get("eType").equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                            tripDetailJson = generalFunc.getJsonValue("MemberDetails", message);

                        } else {
                            tripDetailJson = generalFunc.getJsonValue("driverDetails", message);
                        }
                    } else {
                        tripDetailJson = generalFunc.getJsonValue("driverDetails", message);
                    }

                    if (isBid) {
                        progressHinttext.setText(generalFunc.retrieveLangLBl("Task PROGRESS", "LBL_TASK_PROGRESS"));
                    } else if (!generalFunc.getJsonValue("eType", tripDetailJson).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                        progressHinttext.setText(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_PROGRESS_HINT"));
                    } else {
                        progressHinttext.setText(generalFunc.retrieveLangLBl("Delivery Status", "LBL_DELIVERY_STATUS_TXT"));
                    }

                    vChargesDetailData = generalFunc.getJsonValue("vChargesDetailData", tripDetailJson);
                    if (!Utils.checkText(generalFunc.getJsonValue("totalAmount", vChargesDetailData))) {
                        vChargesDetailData = "";
                    }
                    setData();
                } else {
                    generateErrorView();
                }
            } else {
                generateErrorView();
            }
        });

    }

    private void setView() {
        onGoingTripDetailAdapter = new OnGoingTripDetailAdapter(getActContext(), list, generalFunc);
        onGoingTripsDetailListRecyclerView.setAdapter(onGoingTripDetailAdapter);
        onGoingTripDetailAdapter.notifyDataSetChanged();

    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getTripDeliveryLocations());
    }

    public void closeLoader() {
        if (loadingArea.getVisibility() == View.VISIBLE) {
            loadingArea.setVisibility(View.GONE);
        }
    }

    private void setDriverDetail() {
        String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + generalFunc.getJsonValue("iDriverId", tripDetailJson) + "/" + vImage;
        new LoadImage.builder(LoadImage.bind(image_url), ((ImageView) findViewById(R.id.user_img))).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();


        userNameTxt.setText(vName);
        userAddressTxt.setText(generalFunc.getJsonValue("tSaddress", tripDetailJson));
        ratingBar.setRating(generalFunc.parseFloatValue(0, vRating));

    }

    public void setTimetext(String distance, String time) {
        try {
            String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
            String distance_str = "";
            if (generalFunc.retrieveValue(APP_TYPE).equalsIgnoreCase("UberX") || eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {

                if (googlemaparea.getVisibility() == View.VISIBLE) {
                    timeTxt.setVisibility(View.VISIBLE);
                }


                Logger.d("eUnit", "::" + generalFunc.getJsonValue("eUnit", userProfileJson));
                if (userProfileJson != null && !generalFunc.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {
                    timeTxt.setText(time + " " + generalFunc.retrieveLangLBl("to reach", "LBL_REACH_TXT") + " & " + distance + " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT") + " " + generalFunc.retrieveLangLBl("away", "LBL_AWAY_TXT"));
                } else {
                    timeTxt.setText(time + " " + generalFunc.retrieveLangLBl("to reach", "LBL_REACH_TXT") + " & " + distance + " " + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT") + " " + generalFunc.retrieveLangLBl("away", "LBL_AWAY_TXT"));

                }


            } else {
                if (generalFunc.getJsonValue("eFareType", tripDetailJson).equalsIgnoreCase(Utils.CabFaretypeRegular)) {

                    timeTxt.setVisibility(View.VISIBLE);
                } else {
                    timeTxt.setVisibility(View.GONE);
                }

            }
        } catch (Exception e) {

        }

    }

    private void setLables() {

        titleTxt.setText(generalFunc.retrieveLangLBl("Booking No", "LBL_BOOKING") + "# " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vRideNo", tripDetailJson)));
        subTitleTxt.setVisibility(View.VISIBLE);

        if (generalFunc.getJsonValue("eServiceLocation", tripDetailJson).equalsIgnoreCase("Driver")) {
            subTitleTxt.setText(generalFunc.retrieveLangLBl("Live Track", "LBL_NAVIGATE_TO_PROVIDER"));
        } else {
            subTitleTxt.setText(generalFunc.retrieveLangLBl("Live Track", "LBL_LIVE_TRACK_TXT"));
        }
        subTitleTxt.setVisibility(View.GONE);

        if (isBid) {
            titleTxt.setText(generalFunc.retrieveLangLBl("Task", "LBL_TASK_TXT") + "# " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vBiddingPostNo", tripDetailJson)));
        }

    }

    private void init() {
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        subTitleTxt = (MTextView) findViewById(R.id.subTitleTxt);

        loading_ongoing_trips_detail = (ProgressBar) findViewById(R.id.loading_ongoing_trips_detail);
        loadingArea = (RelativeLayout) findViewById(R.id.loadingArea);
        onGoingTripsDetailListRecyclerView = (RecyclerView) findViewById(R.id.onGoingTripsDetailListRecyclerView);
        errorView = (ErrorView) findViewById(R.id.errorView);
        user_img = (SelectableRoundedImageView) findViewById(R.id.user_img);
        userNameTxt = (MTextView) findViewById(R.id.userNameTxt);
        userAddressTxt = (MTextView) findViewById(R.id.userAddressTxt);
        ratingBar = (SimpleRatingBar) findViewById(R.id.ratingBar);
        progressHinttext = (MTextView) findViewById(R.id.progressHinttext);
        timeTxt = (MTextView) findViewById(R.id.timeTxt);

        googlemaparea = (LinearLayout) findViewById(R.id.googlemaparea);
        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());
        addToClickHandler(backImgView);
        addToClickHandler(subTitleTxt);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        unSubscribeToDriverLocChannel();
    }

    private Activity getActContext() {
        return OnGoingTripDetailsActivity.this;
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {
        this.geoMap = geoMap;

        this.geoMap.setOnMarkerClickListener(marker -> true);


        if (tripDetail != null && !tripDetail.get("vTripStatus").equals("Arrived")) {
            //subscribeToDriverLocChannel();
            LatLng driverLocation_update = new LatLng(Double.parseDouble(tripDetail.get("vLatitude")), Double.parseDouble(tripDetail.get("vLongitude")));

            updateDriverLocation(driverLocation_update);

            Handler handler = new Handler();
            handler.postDelayed(() -> {


            }, 500);

        }


    }

    @Override
    public void onTaskRun(RecurringTask instance) {
        updateDriverLocations();
    }

    public void updateDriverLocations() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getDriverLocations");
        parameters.put("iDriverId", generalFunc.getJsonValue("iDriverId", tripDetailJson));
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    String vLatitude = generalFunc.getJsonValue("vLatitude", responseString);
                    String vLongitude = generalFunc.getJsonValue("vLongitude", responseString);
                    String vTripStatus = generalFunc.getJsonValue("vTripStatus", responseString);

                    if (vTripStatus.equals("Arrived")) {
                        isarrived = true;
                    }

                    LatLng driverLocation_update = new LatLng(GeneralFunctions.parseDoubleValue(0.0, vLatitude),
                            GeneralFunctions.parseDoubleValue(0.0, vLongitude));

                    if (driverMarker != null) {
                        driverMarker.remove();
                    }
                    driverLocation = driverLocation_update;


                    Bitmap bm = BitmapFactory.decodeResource(getResources(), R.mipmap.car_driver).copy(Bitmap.Config.ARGB_8888, true);
                    driverMarker = geoMap.addMarker(
                            new MarkerOptions().position(driverLocation)
                                    .icon(BitmapDescriptorFactory.fromBitmap(bm)));

                    driverMarker.setFlat(true);
                    driverMarker.setAnchor(0.5f, 1);


                    /*}*/

                    geoMap.moveCamera(cameraForDriverPosition());
                }
            } else {
//                    generalFunc.showError();
            }
        });

    }

    public LatLng cameraForDriverPosition() {

        double currentZoomLevel = geoMap == null ? Utils.defaultZomLevel : geoMap.getCameraPosition().zoom;

        if (Utils.defaultZomLevel > currentZoomLevel) {
            currentZoomLevel = Utils.defaultZomLevel;
        }
        if (driverLocation != null) {
            return new LatLng(this.driverLocation.latitude, this.driverLocation.longitude, (float) currentZoomLevel);
        } else {
            return null;
        }
    }

    public void updateDriverLocation(LatLng latlog) {
        if (latlog == null) {
            return;
        }

        if (driverMarker == null) {
            try {
                if (geoMap != null) {
                    marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE))
                            .inflate(R.layout.ufx_live_track, null);
                    providerImgView = (SelectableRoundedImageView) marker_view
                            .findViewById(R.id.providerImgView);


                    driverMarker = geoMap.addMarker(
                            new MarkerOptions().position(latlog)
                                    .title("DriverId" + generalFunc.getJsonValue("iDriverId", tripDetailJson)).
                                    icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view)))
                                    .anchor(0.5f, 1.0f).flat(true));


                    driverLocation = latlog;

                    geoMap.moveCamera(cameraForDriverPosition());


                    providerImgView.setImageResource(R.mipmap.pdefault);

                    driverMarker.setIcon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view)));


                }
            } catch (Exception e) {
                Logger.d("markerException", e.toString());
            }
        } else {
            double currentZoomLevel = geoMap.getCameraPosition().zoom;

            if (Utils.defaultZomLevel > currentZoomLevel) {
                currentZoomLevel = Utils.defaultZomLevel;
            }

            geoMap.animateCamera(latlog.zoom((float) currentZoomLevel));

            Location location = new Location("livetrack");
            location.setLatitude(latlog.latitude);
            location.setLongitude(latlog.longitude);
            animDriverMarker.animateMarker(driverMarker, geoMap, location, 0, defaultMarkerAnimDuration, generalFunc.getJsonValue("iDriverId", tripDetailJson), "");

        }


    }

    public void pubNubMsgArrived(final String message, final Boolean ishow) {

        runOnUiThread(() -> {

            String msgType = generalFunc.getJsonValue("MsgType", message);
            String iDriverId = generalFunc.getJsonValue("iDriverId", message);
            String iTripId = generalFunc.getJsonValue("iTripId", message);

            if (generalFunc.getJsonValue("iDriverId", tripDetailJson).equals(iDriverId)) {
                if (msgType.equals("LocationUpdateOnTrip")) {
                    String vLatitude = generalFunc.getJsonValue("vLatitude", message);
                    String vLongitude = generalFunc.getJsonValue("vLongitude", message);

                    Location driverLoc = new Location("Driverloc");
                    driverLoc.setLatitude(GeneralFunctions.parseDoubleValue(0.0, vLatitude));
                    driverLoc.setLongitude(GeneralFunctions.parseDoubleValue(0.0, vLongitude));

                    Logger.e("DRIVER_STATUS", "::" + isarrived);

                    if (!isarrived) {
                        callUpdateDeirection(driverLoc);
                    } else {
                        timeTxt.setVisibility(View.GONE);
                    }

                    LatLng driverLocation_update = new LatLng(GeneralFunctions.parseDoubleValue(0.0, vLatitude),
                            GeneralFunctions.parseDoubleValue(0.0, vLongitude));

                    driverLocation = driverLocation_update;

                    if (googlemaparea.getVisibility() == View.VISIBLE) {
                        updateDriverLocation(driverLocation_update);
                    }
                } else if (msgType.equals("DriverArrived")) {

                    isarrived = true;

                    if (!generalFunc.getJsonValue("eFareType", tripDetailJson).equals(Utils.CabFaretypeRegular)) {
                        unSubscribeToDriverLocChannel();
                    }

                    if (!isarrivedpopup) {
                        isarrivedpopup = true;
                        getTripDeliveryLocations();
                    }
                } else if (msgType.equals("VerifyCharges")) {
                    vChargesDetailData = message;
                } else if (msgType.equals("VerifyChargesDeclined")) {
                    vChargesDetailData = "";
                } else {
                    onGcmMessageArrived(message, ishow);
                }


            }


        });
    }

    public void onGcmMessageArrived(final String message, boolean ishow) {

        String driverMsg = generalFunc.getJsonValue("Message", message);

        if (driverMsg.equals("DriverArrived")) {
            getTripDeliveryLocations();
        }


        if (driverMsg.equals("TripEnd")) {

            if (ishow) {
                if (!ishowdialog) {

                    GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                    alertBox.setContentMessage("", generalFunc.getJsonValue("vTitle", message));
                    alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                    if (generalFunc.getJsonValue("eType", message).equalsIgnoreCase(Utils.eType_Multi_Delivery) && generalFunc.getJsonValue("Is_Last_Delivery", message).equalsIgnoreCase("Yes")) {

                        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

                    }

                    alertBox.setCancelable(false);
                    alertBox.setBtnClickList(btn_id -> {

                        if (btn_id == 0) {
                            onBackPressed();
                        } else {

                            Bundle bn = new Bundle();
                            bn.putString("iTripId", generalFunc.getJsonValue("iTripId", message));

                            if (generalFunc.getJsonValue("eType", message).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {

                                if (generalFunc.getJsonValue("Is_Last_Delivery", message).equalsIgnoreCase("Yes")) {

                                    if (!Utils.checkText(generalFunc.getJsonValue("iTripId", message))) {
                                        return;
                                    }

                                    bn.putBoolean("isUfx", false);
                                    ishowdialog = false;
                                    new ActUtils(getActContext()).startActForResult(HistoryDetailActivity.class, bn, Utils.MULTIDELIVERY_HISTORY_RATE_CODE);
                                } else if (generalFunc.getJsonValue("Is_Last_Delivery", message).equalsIgnoreCase("NO")) {
                                    getTripDeliveryLocations();
                                    return;
                                }

                            } else {
                                ishowdialog = false;

                                String eType = generalFunc.getJsonValue("eType", message);
                                Logger.d("eTypeNotiFication", "::" + eType);
                                if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                                    bn.putBoolean("isUfx", true);
                                } else {
                                    bn.putBoolean("isUfx", false);
                                }

                                bn.putString("iTripId", generalFunc.getJsonValue("iTripId", message));
                                new ActUtils(getActContext()).startActWithData(RatingActivity.class, bn);
                                finish();
                            }

                        }

                    });
                    alertBox.showAlertBox();
                }
            }

        } else if (driverMsg.equals("TripStarted")) {

            getTripDeliveryLocations();


        } else if (driverMsg.equals("VerifyCharges")) {
            vChargesDetailData = message;
        } else if (driverMsg.equals("VerifyChargesDeclined")) {
            vChargesDetailData = "";
        } else if (driverMsg.equals("TripCancelledByDriver") || driverMsg.equalsIgnoreCase("TripCancelled")) {

            if (ishow) {
                if (!ishowdialog) {
                    ishowdialog = true;
                    String reason = generalFunc.getJsonValue("Reason", message);


                    GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                    alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_PREFIX_TRIP_CANCEL_DRIVER") + " " + reason);
                    alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                    alertBox.setCancelable(false);
                    alertBox.setBtnClickList(btn_id -> {


                        String eType = generalFunc.getJsonValue("eType", message);
                        if (generalFunc.getJsonValue("ShowTripFare", message).equalsIgnoreCase("true") || (!eType.equalsIgnoreCase(Utils.eType_Multi_Delivery) && !eType.equalsIgnoreCase(Utils.CabGeneralType_UberX))) {
                            Bundle bn = new Bundle();
                            Logger.d("eTypeNotiFication", "::" + eType);
                            if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                                bn.putBoolean("isUfx", true);
                            } else {
                                bn.putBoolean("isUfx", false);
                            }

                            bn.putString("iTripId", generalFunc.getJsonValue("iTripId", message));
                            new ActUtils(getActContext()).startActWithData(RatingActivity.class, bn);
                            finish();
                        } else {
                            backImgView.performClick();
                        }

                    });

                    alertBox.showAlertBox();
                }
            }
        }
    }

    @Override
    public void onBackPressed() {
        if (isBid) {
            if (getIntent().getBooleanExtra("isBack", false)) {
                super.onBackPressed();
            } else {
                MyApp.getInstance().restartWithGetDataApp();
            }
        } else {
            super.onBackPressed();
        }
    }


    public void onClick(View view) {
        if (view.getId() == R.id.backImgView) {
            onBackPressed();
        } else if (view.getId() == R.id.subTitleTxt) {
            MyApp.getInstance().restartWithGetDataApp(getIntent().getStringExtra("iTripId"));
        }
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.MULTIDELIVERY_HISTORY_RATE_CODE) {
            finish();
        } else if (requestCode == ADDITIONAL_CHARGES_CODE && resultCode == RESULT_OK) {
            finish();
        }
    }

    public void verifyCharges(String message) {
        redirectToDetailCharges(message);
    }

}
