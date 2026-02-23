package com.fragments;


import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Intent;
import android.database.Cursor;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.provider.ContactsContract;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.fragment.app.Fragment;

import com.act.MainActivity;
import com.dialogs.OpenListView;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.DialogPickupPhotoRequestBinding;
import com.model.ChatMsgHandler;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.view.editBox.MaterialEditText;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * A simple {@link Fragment} subclass.
 */
public class DriverDetailFragment extends BaseFragment implements GetAddressFromLocation.AddressFound, ViewTreeObserver.OnGlobalLayoutListener {

    int PICK_CONTACT = 2121;

    View view;
    MainActivity mainAct;
    GeneralFunctions generalFunc;

    String driverPhoneNum = "";

    DriverDetailFragment driverDetailFragment;

    String userProfileJson;

    String vDeliveryConfirmCode = "";


    // View contactview;
    SimpleRatingBar ratingBar;
    GetAddressFromLocation getAddressFromLocation;
    HashMap<String, String> tripDataMap;
    public int fragmentWidth = 0;
    public int fragmentHeight = 0;
    AlertDialog dialog_declineOrder;
    boolean isCancelTripWarning = true;
    String vImage = "";
    String vName = "";
    private String recipientNameTxt = "";

    private static final String TAG = "DriverDetailFragment";
    public ImageView rlCall, rlMessage, rlCancel, rlShare, confirmationareacode;


    RelativeLayout fragmentMainLayout;
    public int fragmentBottomAreaHeight = 0, fragmentBottomAreaWidth = 0;

    @Nullable
    private Dialog dialogPickupPhotoReply;
    private Boolean isTappedOnce;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_driver_detail, container, false);


        ratingBar = (SimpleRatingBar) view.findViewById(R.id.ratingBar);
        fragmentMainLayout = view.findViewById(R.id.fragmentMainLayout);
        isTappedOnce = false;

        rlCall = (ImageView) view.findViewById(R.id.rlCall);
        rlMessage = (ImageView) view.findViewById(R.id.rlMessage);
        rlShare = (ImageView) view.findViewById(R.id.rlShare);
        rlCancel = (ImageView) view.findViewById(R.id.rlCancel);

        addToClickHandler(rlCall);
        addToClickHandler(rlMessage);
        addToClickHandler(rlShare);
        addToClickHandler(rlCancel);

        confirmationareacode = (ImageView) view.findViewById(R.id.confirmationareacode);
        addToClickHandler(confirmationareacode);

        mainAct = (MainActivity) getActivity();
        userProfileJson = mainAct.obj_userProfile.toString();
        generalFunc = mainAct.generalFunc;


        getAddressFromLocation = new GetAddressFromLocation(getActivity(), generalFunc);
        getAddressFromLocation.setAddressList(this);

        setData();

        addGlobalLayoutListner();

        driverDetailFragment = mainAct.getDriverDetailFragment();

        mainAct.setDriverImgView(((SelectableRoundedImageView) view.findViewById(R.id.driverImgView)));

        if (generalFunc.getJsonValue("vTripStatus", userProfileJson).equals("On Going Trip")) {

            configTripStartView(vDeliveryConfirmCode);

        }

        new Handler().postDelayed(() -> {
            fragmentBottomAreaHeight = fragmentMainLayout.getMeasuredHeight();
            fragmentBottomAreaWidth = fragmentMainLayout.getMeasuredWidth();
            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) (mainAct.userLocBtnImgView).getLayoutParams();
            params.bottomMargin = fragmentBottomAreaHeight + mainAct.getResources().getDimensionPixelSize(R.dimen._15sdp);
            mainAct.userLocBtnImgView.requestLayout();
        }, 100);

        String OPEN_CHAT = generalFunc.retrieveValue(ChatMsgHandler.OPEN_CHAT);
        if (Utils.checkText(OPEN_CHAT)) {
            JSONObject OPEN_CHAT_DATA_OBJ = generalFunc.getJsonObject(OPEN_CHAT);
            if (OPEN_CHAT_DATA_OBJ != null) {
                dialogPickupPhotoReplyView(OPEN_CHAT_DATA_OBJ);
            }
        }

        return view;
    }

    @SuppressLint("SetTextI18n")
    public void dialogPickupPhotoReplyView(JSONObject obj_data) {

        if (generalFunc.retrieveValue(ChatMsgHandler.PICKUP_REPLY_VIEW).equalsIgnoreCase("Yes")) {
            if (dialogPickupPhotoReply != null && dialogPickupPhotoReply.isShowing()) {
                return;
            }

            dialogPickupPhotoReply = new Dialog(mainAct, R.style.ImageSourceDialogStyle);
            @NonNull DialogPickupPhotoRequestBinding dPBinding = DialogPickupPhotoRequestBinding.inflate(LayoutInflater.from(mainAct), null, false);
            //----

            new LoadImage.builder(LoadImage.bind(Utils.checkText(vImage) ? vImage : "Temp"), dPBinding.driverImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

            dPBinding.driverNameTxt.setText(tripDataMap.get("DriverName"));
            dPBinding.driverCarModelTxt.setText(tripDataMap.get("DriverCarName") + " - " + tripDataMap.get("DriverCarModelName"));
            dPBinding.numberPlateVTxt.setText(tripDataMap.get("DriverCarPlateNum"));
            dPBinding.msgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TAKE_PICTURE_FOR_AREA"));

            MButton closeBtn = ((MaterialRippleLayout) dPBinding.closeBtn).getChildView();
            closeBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CLOSE_TXT"));
            closeBtn.setOnClickListener(v -> {
                generalFunc.removeValue(ChatMsgHandler.PICKUP_REPLY_VIEW);
                dialogPickupPhotoReply.cancel();
            });

            MButton replyBtn = ((MaterialRippleLayout) dPBinding.replyBtn).getChildView();
            replyBtn.setText(generalFunc.retrieveLangLBl("", "LBL_REPLY_TXT"));
            replyBtn.setOnClickListener(v -> {
                if (obj_data != null) {
                    try {
                        obj_data.put("isOpenMediaDialog", "Yes");
                    } catch (JSONException e) {
                        throw new RuntimeException(e);
                    }
                    ChatMsgHandler.openChatAct(obj_data);
                    closeBtn.performClick();
                }
            });

            //----
            dialogPickupPhotoReply.setContentView(dPBinding.getRoot());

            dialogPickupPhotoReply.setCanceledOnTouchOutside(false);
            dialogPickupPhotoReply.setCancelable(false);
            LayoutDirection.setLayoutDirection(dialogPickupPhotoReply);
            dialogPickupPhotoReply.show();
        }
    }

    private boolean isMultiDelivery() {
        if (tripDataMap == null) {
            if (getTripData() != null) {
                this.tripDataMap = getTripData();
                return tripDataMap.get("eType").equalsIgnoreCase(Utils.eType_Multi_Delivery);
            }
        }
        return false;
    }

    public HashMap<String, String> getTripData() {

        HashMap<String, String> tripDataMap = (HashMap<String, String>) getArguments().getSerializable("TripData");
        return tripDataMap;
    }

    public void setData() {
        tripDataMap = (HashMap<String, String>) getArguments().getSerializable("TripData");

        ((MTextView) view.findViewById(R.id.driver_car_model)).setText(tripDataMap.get("DriverCarModelName"));

        if (tripDataMap.get("eFly") != null && tripDataMap.get("eFly").equalsIgnoreCase("Yes")) {
            rlShare.setVisibility(View.GONE);
        }

        vName = tripDataMap.get("DriverName");
        String name = tripDataMap.get("DriverName");
        String carColor = tripDataMap.get("DriverCarColour");
        Log.d(TAG, "DriverName: " + name);

        ((MTextView) view.findViewById(R.id.driver_name)).setText(tripDataMap.get("DriverName"));
        ratingBar.setRating(generalFunc.parseFloatValue(0, tripDataMap.get("DriverRating")));
        ((MTextView) view.findViewById(R.id.driver_car_model)).setText(tripDataMap.get("DriverCarName") + " - " + tripDataMap.get("DriverCarModelName"));
        ((MTextView) view.findViewById(R.id.numberPlate_txt)).setText(tripDataMap.get("DriverCarPlateNum"));
        ((MTextView) view.findViewById(R.id.driver_car_type)).setText(Utils.checkText(carColor) ? carColor : tripDataMap.get("vVehicleType"));
        driverPhoneNum = tripDataMap.get("DriverPhone");
        vDeliveryConfirmCode = tripDataMap.get("vDeliveryConfirmCode");
        String driverImageName = tripDataMap.get("DriverImage");

        if (isMultiDelivery()) {
            /*Set delivery recipient Detail*/
            recipientNameTxt = tripDataMap.get("recipientNameTxt");

            Logger.d("Api", "recipient Name" + recipientNameTxt);
            if (recipientNameTxt != null && Utils.checkText(recipientNameTxt)) {
                view.findViewById(R.id.recipientNameArea).setVisibility(View.VISIBLE);
                ((MTextView) view.findViewById(R.id.recipientNameTxt)).setText(recipientNameTxt);
            }
        }


        if (generalFunc.getJsonValueStr("eSignVerification", generalFunc.getJsonObject("TripDetails", userProfileJson)).equals("Yes")) {

            configTripStartView(vDeliveryConfirmCode);

        }

        if (driverImageName == null || driverImageName.equals("") || driverImageName.equals("NONE")) {
            ((SelectableRoundedImageView) view.findViewById(R.id.driverImgView)).setImageResource(R.mipmap.ic_no_pic_user);
            vImage = "";
        } else {
            String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + tripDataMap.get("iDriverId") + "/"
                    + tripDataMap.get("DriverImage");
            vImage = image_url;

            new LoadImage.builder(LoadImage.bind(image_url), ((SelectableRoundedImageView) view.findViewById(R.id.driverImgView))).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        }
    }

    public String getDriverPhone() {
        return driverPhoneNum;
    }

    public void configTripStartView(String vDeliveryConfirmCode) {

        rlCancel.setVisibility(View.GONE);
        if (mainAct != null && mainAct.driverAssignedHeaderFrag != null) {
            mainAct.driverAssignedHeaderFrag.otpInfoArea.setVisibility(View.GONE);
        }

        if (mainAct != null && !vDeliveryConfirmCode.trim().equals("") && !ServiceModule.isServiceProviderOnly()) {

            this.vDeliveryConfirmCode = vDeliveryConfirmCode;
            confirmationareacode.setVisibility(View.VISIBLE);
        }


        if (isMultiDelivery() && recipientNameTxt != null && Utils.checkText(recipientNameTxt) && Utils.checkText(vDeliveryConfirmCode)) {
            confirmationareacode.setVisibility(View.VISIBLE);
        }
        if (generalFunc.getJsonValue("eType", userProfileJson).equalsIgnoreCase(Utils.CabGeneralType_Ride) && generalFunc.getJsonValue("ENABLE_PROVIDER_CAMERA_REC", userProfileJson).equalsIgnoreCase("Yes")) {
            view.findViewById(R.id.videoRecordingNoteTxt).setVisibility(View.VISIBLE);
            ((MTextView) view.findViewById(R.id.videoRecordingNoteTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_VEHICLE_EQUIPPED_VIDEO_REC_TXT"));
        } else {
            view.findViewById(R.id.videoRecordingNoteTxt).setVisibility(View.GONE);
        }

    }

    public void call(String phoneNumber) {
        try {

            Intent callIntent = new Intent(Intent.ACTION_DIAL);
            callIntent.setData(Uri.parse("tel:" + phoneNumber));
            startActivity(callIntent);

        } catch (Exception e) {
            // TODO: handle exception
        }
    }


    public void cancelTrip(String eConfirmByUser, String iCancelReasonId, String reason) {
        HashMap<String, String> tripDataMap = (HashMap<String, String>) getArguments().getSerializable("TripData");


        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "cancelTrip");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iDriverId", tripDataMap.get("iDriverId"));
        parameters.put("iTripId", tripDataMap.get("iTripId"));
        parameters.put("eConfirmByUser", eConfirmByUser);
        parameters.put("iCancelReasonId", iCancelReasonId);
        parameters.put("Reason", reason);

        ApiHandler.execute(mainAct.getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail) {

                    GenerateAlertBox generateAlert = new GenerateAlertBox(mainAct.getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> MyApp.getInstance().refreshView(mainAct, responseString));
                    String msg = "";

                    if (tripDataMap.get("eType").equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
                        msg = generalFunc.retrieveLangLBl("", "LBL_SUCCESS_TRIP_CANCELED");
                    } else if (tripDataMap.get("eType").equalsIgnoreCase("Deliver") || isMultiDelivery()) {
                        msg = generalFunc.retrieveLangLBl("", "LBL_SUCCESS_DELIVERY_CANCELED");

                    } else {
                        msg = generalFunc.retrieveLangLBl("", "LBL_SUCCESS_TRIP_CANCELED");
                    }
                    generateAlert.setContentMessage("", msg);
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();


                } else {

                    if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY) || message.equals("LBL_SERVER_COMM_ERROR")) {

                        MyApp.getInstance().restartWithGetDataApp();
                        return;
                    }


                    if (generalFunc.getJsonValue("isCancelChargePopUpShow", responseString).equalsIgnoreCase("Yes")) {

                        final GenerateAlertBox generateAlert = new GenerateAlertBox(mainAct.getActContext());
                        generateAlert.setCancelable(false);
                        generateAlert.setBtnClickList(btn_id -> {
                            if (btn_id == 0) {
                                generateAlert.closeAlertBox();

                            } else {
                                generateAlert.closeAlertBox();
                                cancelTrip("Yes", iCancelReasonId, reason);

                            }

                        });
                        generateAlert.setContentMessage("", generalFunc.convertNumberWithRTL(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString))));
                        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
                        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
                        generateAlert.showAlertBox();

                        return;
                    }
                    isCancelTripWarning = false;
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == PICK_CONTACT && data != null) {
            Uri uri = data.getData();

            if (uri != null) {
                Cursor c = null;
                try {
                    c = mainAct.getContentResolver().query(uri, new String[]{ContactsContract.CommonDataKinds.Phone.NUMBER,
                            ContactsContract.CommonDataKinds.Phone.TYPE}, null, null, null);

                    if (c != null && c.moveToFirst()) {
                        String number = c.getString(0);

                        Intent smsIntent = new Intent(Intent.ACTION_VIEW);
                        smsIntent.setType("vnd.android-dir/mms-sms");
                        smsIntent.putExtra("address", "" + number);

                        String link_location = "http://maps.google.com/?q=" + mainAct.userLocation.getLatitude() + "," + mainAct.userLocation.getLongitude();
                        smsIntent.putExtra("sms_body", generalFunc.retrieveLangLBl("", "LBL_SEND_STATUS_CONTENT_TXT") + " " + link_location);
                        startActivity(smsIntent);
                    }
                } finally {
                    if (c != null) {
                        c.close();
                    }
                }
            }

        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        Utils.hideKeyboard(getActivity());
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {

        Intent sharingIntent = new Intent(Intent.ACTION_SEND);
        sharingIntent.setType("text/plain");
        sharingIntent.putExtra(Intent.EXTRA_SUBJECT, "");
        String link_location = "";
        if (Utils.checkText(generalFunc.getJsonValue("liveTrackingUrl", userProfileJson))) {
            link_location = generalFunc.getJsonValue("liveTrackingUrl", userProfileJson);
        } else {
            link_location = "http://maps.google.com/?q=" + address.replace(" ", "%20");
        }
        isTappedOnce = false;
        sharingIntent.putExtra(Intent.EXTRA_TEXT, generalFunc.retrieveLangLBl("", "LBL_SEND_STATUS_CONTENT_TXT") + " " + link_location);
        startActivity(Intent.createChooser(sharingIntent, generalFunc.retrieveLangLBl("", "LBL_SHARE_USING")));

    }

    @Override
    public void onResume() {
        super.onResume();
        addGlobalLayoutListner();
    }

    @Override
    public void onGlobalLayout() {
        boolean heightChanged = false;
        if (getView() != null || view != null) {
            if (getView() != null) {

                if (getView().getHeight() != 0 && getView().getHeight() != fragmentHeight) {
                    heightChanged = true;
                }
                fragmentWidth = getView().getWidth();
                fragmentHeight = getView().getHeight();
            } else if (view != null) {

                if (view.getHeight() != 0 && view.getHeight() != fragmentHeight) {
                    heightChanged = true;
                }

                fragmentWidth = view.getWidth();
                fragmentHeight = view.getHeight();
            }

            Logger.e("FragHeight", "is :::" + fragmentHeight + "\n" + "Frag Width is :::" + fragmentWidth);

            if (heightChanged && fragmentWidth != 0 && fragmentHeight != 0) {
                mainAct.setPanelHeight(fragmentHeight);
            }
        }
    }

    private void addGlobalLayoutListner() {
        if (getView() != null) {
            getView().getViewTreeObserver().removeGlobalOnLayoutListener(this);
        }
        if (view != null) {
            view.getViewTreeObserver().removeGlobalOnLayoutListener(this);
        }

        if (getView() != null) {
            getView().getViewTreeObserver().addOnGlobalLayoutListener(this);
        } else if (view != null) {
            view.getViewTreeObserver().addOnGlobalLayoutListener(this);
        }
    }

    public void getDeclineReasonsList() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetCancelReasons");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("eUserType", Utils.app_type);
        parameters.put("eJobType", generalFunc.getJsonValue("eJobType", generalFunc.getJsonValue("TripDetails", mainAct.obj_userProfile.toString())));

        parameters.put("iTripId", tripDataMap.get("iTripId"));

        ApiHandler.execute(mainAct.getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    showDeclineReasonsAlert(responseString);
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
    }


    int selCurrentPosition = -1;

    public void showDeclineReasonsAlert(String responseString) {
        if (dialog_declineOrder != null) {
            if (dialog_declineOrder.isShowing()) {
                dialog_declineOrder.dismiss();
            }
            dialog_declineOrder = null;
        }
        selCurrentPosition = -1;
        String titleDailog = "";
        if (tripDataMap.get("eType").equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            titleDailog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TRIP");
        } else if (tripDataMap.get("eType").equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            titleDailog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_BOOKING");
        } else {
            titleDailog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_DELIVERY");
        }


        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(mainAct);

        LayoutInflater inflater = requireActivity().getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = (MaterialEditText) dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = (RelativeLayout) dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            reasonBox.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));


        ArrayList<HashMap<String, String>> list = new ArrayList<HashMap<String, String>>();
        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
        if (arr_msg != null) {

            for (int i = 0; i < arr_msg.length(); i++) {

                JSONObject obj_tmp = generalFunc.getJsonObject(arr_msg, i);
                HashMap<String, String> datamap = new HashMap<>();
                datamap.put("title", generalFunc.getJsonValueStr("vTitle", obj_tmp));
                datamap.put("id", generalFunc.getJsonValueStr("iCancelReasonId", obj_tmp));
                list.add(datamap);
            }

            HashMap<String, String> othermap = new HashMap<>();
            othermap.put("title", generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
            othermap.put("id", "");
            list.add(othermap);

            MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);
            MTextView submitTxt = (MTextView) dialogView.findViewById(R.id.submitTxt);
            MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);
            ImageView cancelImg = (ImageView) dialogView.findViewById(R.id.cancelImg);
            MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
            subTitleTxt.setText(titleDailog);

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

                if (Utils.checkText(reasonBox) == false && selCurrentPosition == (list.size() - 1)) {
                    errorTextView.setVisibility(View.VISIBLE);
                    errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                    return;
                }


                cancelTrip("No", list.get(selCurrentPosition).get("id"), reasonBox.getText().toString().trim());

                dialog_declineOrder.dismiss();


            });
            cancelTxt.setOnClickListener(v -> {
                errorTextView.setVisibility(View.GONE);
                dialog_declineOrder.dismiss();
            });

            cancelImg.setOnClickListener(v -> {
                errorTextView.setVisibility(View.GONE);
                dialog_declineOrder.dismiss();
            });


            declinereasonBox.setOnClickListener(v -> OpenListView.getInstance(getActivity(), generalFunc.retrieveLangLBl("", "LBL_SELECT_REASON"), list, OpenListView.OpenDirection.CENTER, true, position -> {


                selCurrentPosition = position;
                HashMap<String, String> mapData = list.get(position);
                errorTextView.setVisibility(View.GONE);
                declinereasonBox.setText(mapData.get("title"));
                if (selCurrentPosition == (list.size() - 1)) {
                    reasonBox.setVisibility(View.VISIBLE);
                    commentArea.setVisibility(View.VISIBLE);
                } else {
                    reasonBox.setVisibility(View.GONE);
                    commentArea.setVisibility(View.GONE);
                }

                submitTxt.setClickable(true);
                submitTxt.setTextColor(getResources().getColor(R.color.white));


            }).show(selCurrentPosition, "title"));


            dialog_declineOrder = builder.create();
            dialog_declineOrder.setCancelable(false);
            dialog_declineOrder.getWindow().setBackgroundDrawable(getActivity().getResources().getDrawable(R.drawable.all_roundcurve_card));
            LayoutDirection.setLayoutDirection(dialog_declineOrder);
            dialog_declineOrder.show();

        } else {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
        }
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int id = view.getId();
        if (id == R.id.rlCall || id == R.id.rlMessage) {
            JSONObject tripDetailJson = generalFunc.getJsonObject("TripDetails", mainAct.obj_userProfile);
            String vBookingNo = generalFunc.getJsonValueStr("vBookingNo", tripDetailJson);
            String vRideNo = generalFunc.getJsonValueStr("vRideNo", tripDetailJson);

            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(tripDataMap.get("iDriverId"))
                    .setPhoneNumber(tripDataMap.get("DriverPhone"))
                    .setToMemberType(Utils.CALLTODRIVER)
                    .setToMemberName(tripDataMap.get("DriverName"))
                    .setToMemberImage(tripDataMap.get("DriverImage"))
                    .setMedia(tripDataMap.get("eBookingFrom").equalsIgnoreCase("Kiosk") ? CommunicationManager.MEDIA.DEFAULT : CommunicationManager.MEDIA_TYPE)
                    .setTripId(tripDataMap.get("iTripId"))
                    .setBookingNo(Utils.checkText(vBookingNo) ? vBookingNo : vRideNo)
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, view.getId() == R.id.rlMessage ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);
        } else if (id == R.id.rlShare) {
            if (isTappedOnce) {
                return;
            }
            if (mainAct != null && mainAct.driverAssignedHeaderFrag != null && mainAct.driverAssignedHeaderFrag.driverLocation != null) {
                isTappedOnce = true;
                getAddressFromLocation.setLocation(mainAct.driverAssignedHeaderFrag.driverLocation.latitude, mainAct.driverAssignedHeaderFrag.driverLocation.longitude);
                getAddressFromLocation.setLoaderEnable(true);
                getAddressFromLocation.execute();
            }
        } else if (id == R.id.rlCancel) {
            String msg = "";

            if (tripDataMap.get("eType").equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
                msg = generalFunc.retrieveLangLBl("", "LBL_TRIP_CANCEL_TXT");
            } else {
                msg = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_CANCEL_TXT");
            }

            isCancelTripWarning = true;
            getDeclineReasonsList();
        } else if (id == R.id.confirmationareacode) {
            showCodeDialog();
        }
    }


    private void showCodeDialog() {

        // vDeliveryConfirmCode
        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Delivery Confirmation Code", "LBL_DELIVERY_CONFIRMATION_CODE_TXT"),
                generalFunc.retrieveLangLBl("", generalFunc.convertNumberWithRTL(vDeliveryConfirmCode)));


    }

    private GradientDrawable getRoundBG(String color) {

        int strokeWidth = 2;
        int strokeColor = Color.parseColor("#CCCACA");
        int fillColor = Color.parseColor(color);
        GradientDrawable gD = new GradientDrawable();
        gD.setColor(fillColor);
        gD.setShape(GradientDrawable.RECTANGLE);
        gD.setCornerRadius(100);
        gD.setStroke(strokeWidth, strokeColor);

        return gD;
    }


}
