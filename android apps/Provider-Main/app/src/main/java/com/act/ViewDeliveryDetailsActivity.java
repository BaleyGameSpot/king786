package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import com.activity.ParentActivity;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.ErrorView;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import java.util.HashMap;

public class ViewDeliveryDetailsActivity extends ParentActivity {

    MTextView titleTxt;
    ImageView backImgView;
    ErrorView errorView;
    ProgressBar loading;
    View contentArea;
    String data_message;

    HashMap<String, String> trip_data;
    String vImage = "";
    String vName = "";
    SelectableRoundedImageView userProfileImgView;
    private LinearLayout chatArea, callArea, receiverCallArea, receiverMsgArea;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_view_delivery_details);

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        contentArea = findViewById(R.id.contentArea);
        errorView = (ErrorView) findViewById(R.id.errorView);
        loading = (ProgressBar) findViewById(R.id.loading);

        addToClickHandler(backImgView);

        setLabels();

        getDeliveryData();
        userProfileImgView = findViewById(R.id.userProfileImgView);


        chatArea = (LinearLayout) findViewById(R.id.chatArea);
        callArea = (LinearLayout) findViewById(R.id.callArea);

        addToClickHandler(chatArea);
        addToClickHandler(callArea);


        receiverCallArea = (LinearLayout) findViewById(R.id.receiverCallArea);
        receiverMsgArea = (LinearLayout) findViewById(R.id.receiverMsgArea);
        addToClickHandler(receiverCallArea);
        addToClickHandler(receiverMsgArea);

        int transpenrent = getActContext().getResources().getColor(R.color.mdtp_transparent_full);
        int white = getActContext().getResources().getColor(R.color.white);
        //int bordercolor = getActContext().getResources().getColor(R.color.gray_holo_light);
        int cornorRadius = Utils.dipToPixels(getActContext(), 5);
        int strokeWidth = Utils.dipToPixels(getActContext(), 1);

        new CreateRoundedView(transpenrent, cornorRadius, strokeWidth, white, chatArea);
        new CreateRoundedView(white, cornorRadius, strokeWidth, white, callArea);

        new CreateRoundedView(white, cornorRadius, strokeWidth, white, receiverCallArea);
        new CreateRoundedView(white, cornorRadius, strokeWidth, white, receiverMsgArea);


        trip_data = (HashMap<String, String>) getIntent().getSerializableExtra("data_trip");


        vName = trip_data.get("PName");

        if (!vName.equals("")) {
            vImage = CommonUtilities.USER_PHOTO_PATH + trip_data.get("PassengerId") + "/"
                    + vName;
        }

    }

    public void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Delivery Details", "LBL_DELIVERY_DETAILS"));

        // ((MTextView) findViewById(R.id.senderHTxt)).setText(generalFunc.retrieveLangLBl("Sender", "LBL_SENDER"));
        ((MTextView) findViewById(R.id.senderCallTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"));
        ((MTextView) findViewById(R.id.senderMsgTxt)).setText(generalFunc.retrieveLangLBl("Message", "LBL_MESSAGE_TXT"));
//        ((MTextView) findViewById(R.id.senderMsgTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_MESSAGE_TXT"));
        ((MTextView) findViewById(R.id.receiverHTxt)).setText(generalFunc.retrieveLangLBl("Recipient", "LBL_RECIPIENT"));
        ((MTextView) findViewById(R.id.receiverCallTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"));
        ((MTextView) findViewById(R.id.receiverMsgTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_MESSAGE_TXT"));
        ((MTextView) findViewById(R.id.packageTypeHTxt)).setText(generalFunc.retrieveLangLBl("Package Type", "LBL_PACKAGE_TYPE"));
        ((MTextView) findViewById(R.id.packageDetailsHTxt)).setText(generalFunc.retrieveLangLBl("Package Details", "LBL_PACKAGE_DETAILS"));
        ((MTextView) findViewById(R.id.pickUpInsHTxt)).setText(generalFunc.retrieveLangLBl("Pickup instruction", "LBL_PICK_UP_INS"));
        ((MTextView) findViewById(R.id.deliveryInsHTxt)).setText(generalFunc.retrieveLangLBl("Delivery instruction", "LBL_DELIVERY_INS"));


    }

    public void getDeliveryData() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (contentArea.getVisibility() == View.VISIBLE) {
            contentArea.setVisibility(View.GONE);
        }
        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "loadDeliveryDetails");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("iTripId", getIntent().getStringExtra("TripId"));
        parameters.put("appType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        closeLoader();

                        if (generalFunc.checkDataAvail(Utils.action_str, responseString)) {

                            setData(generalFunc.getJsonValue(Utils.message_str, responseString));
                        } else {
                            generateErrorView();
                        }
                    } else {
                        generateErrorView();
                    }
                });
    }

    public void closeLoader() {
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
        }
    }

    public void setData(String message) {

        this.data_message = message;

        ((MTextView) findViewById(R.id.senderNameTxt)).setText(generalFunc.getJsonValue("senderName", message));
        ((MTextView) findViewById(R.id.senderMobileTxt)).setText(generalFunc.getJsonValue("senderMobile", message));
        ((MTextView) findViewById(R.id.receiverNameTxt)).setText(generalFunc.getJsonValue("vReceiverName", message));
        ((MTextView) findViewById(R.id.receiverMobileTxt)).setText(generalFunc.getJsonValue("vReceiverMobile", message));
        ((MTextView) findViewById(R.id.packageTypeVTxt)).setText(generalFunc.getJsonValue("packageType", message));
        ((MTextView) findViewById(R.id.packageDetailsVTxt)).setText(generalFunc.getJsonValue("tPackageDetails", message));
        ((MTextView) findViewById(R.id.pickUpInsVTxt)).setText(generalFunc.getJsonValue("tPickUpIns", message));
        ((MTextView) findViewById(R.id.deliveryInsVTxt)).setText(generalFunc.getJsonValue("tDeliveryIns", message));

        String imagePath = generalFunc.getJsonValue("vImage", message);
        String vImage = Utils.checkText(imagePath) ? imagePath : "https";
        new LoadImage.builder(LoadImage.bind(vImage), userProfileImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        contentArea.setVisibility(View.VISIBLE);
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getDeliveryData());
    }

    public Context getActContext() {
        return ViewDeliveryDetailsActivity.this;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(ViewDeliveryDetailsActivity.this);
        int id = view.getId();
        if (id == R.id.backImgView) {
            ViewDeliveryDetailsActivity.super.onBackPressed();
        } else if (id == R.id.callArea || id == R.id.chatArea) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(trip_data.get("PassengerId"))
                    .setPhoneNumber(view.getId() == R.id.chatArea ? generalFunc.getJsonValue("senderMobile", data_message) : CommunicationManager.MEDIA_TYPE == CommunicationManager.MEDIA.DEFAULT ? generalFunc.getJsonValue("senderMobile", data_message) : trip_data.get("vPhone_U"))
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(trip_data.get("PName"))
                    .setToMemberImage(trip_data.get("PPicName"))
                    .setMedia(!Utils.checkText(trip_data.get("iGcmRegId_U")) ? CommunicationManager.MEDIA.DEFAULT : CommunicationManager.MEDIA_TYPE)
                    .setTripId(trip_data.get("iTripId"))
                    .setBookingNo(trip_data.get("vRideNo"))
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, view.getId() == R.id.chatArea ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);
        } else if (id == R.id.receiverCallArea || id == R.id.receiverMsgArea) {
            MediaDataProvider mDataProvider1 = new MediaDataProvider.Builder()
                    .setPhoneNumber(generalFunc.getJsonValue("vReceiverMobileOriginal", data_message))
                    .setToMemberName(Utils.getText(((MTextView) findViewById(R.id.receiverNameTxt))))
                    .setMedia(CommunicationManager.MEDIA.DEFAULT).build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider1, view.getId() == R.id.chatArea ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);
        }
    }

}
