package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.drawable.ColorDrawable;
import android.net.Uri;
import android.os.Bundle;
import android.text.InputFilter;
import android.text.InputType;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.BidAdditionalMediaAdapter;
import com.dialogs.BottomScheduleDialog;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRequestBidInfoBinding;
import com.buddyverse.main.databinding.DialogMediaSelectedBinding;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.LayoutDirection;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;

public class RequestBidInfoActivity extends ParentActivity {

    private ActivityRequestBidInfoBinding binding;
    private String iUserAddressId;
    private Date selectedDate;
    private BottomScheduleDialog bottomScheduleDialog;
    private BidAdditionalMediaAdapter mediaAdapter;
    private final JSONArray mediaListArray = new JSONArray();
    private MButton bidCancelBtn, bidPostBtn;
    private String ibiddingPostMediaId = "";
    String ShowAdjustTripBtn;
    String ShowPayNow;
    String ShowContactUsBtn;
    AlertDialog outstanding_dialog;
    private static final int WEBVIEWPAYMENT = 001;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_request_bid_info);

        initViews();
        setLabel();
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_POST_TASK_TITLE"));

        bidCancelBtn = ((MaterialRippleLayout) findViewById(R.id.bidCancelBtn)).getChildView();
        bidCancelBtn.setId(Utils.generateViewId());
        addToClickHandler(bidCancelBtn);

        bidPostBtn = ((MaterialRippleLayout) findViewById(R.id.bidPostBtn)).getChildView();
        bidPostBtn.setId(Utils.generateViewId());
        addToClickHandler(bidPostBtn);

        bottomScheduleDialog = new BottomScheduleDialog(this, (selDateTime, date, iCabBookingId) -> {
            binding.dateTimeEditBox.setText(Utils.convertDateToFormat(DateTimeUtils.DateTimeFormat, date));
            selectedDate = date;
        });

        // Additional Media
        if (generalFunc.getJsonValueStr("ENABLE_UPLOAD_MEDIA_BIDDING", obj_userProfile).equalsIgnoreCase("Yes")) {
            binding.additionalMediaArea.setVisibility(View.VISIBLE);
            addViewAdd();
            mediaAdapter = new BidAdditionalMediaAdapter(getActContext(), generalFunc, mediaListArray, 4, true, new BidAdditionalMediaAdapter.OnItemClickListener() {
                @Override
                public void onItemClickList(int position, JSONObject itemObject) {
                    if (position == 0) {
                        openMediaSelectedDialog();
                    } else {
                        new ActUtils(getActContext()).openURL(generalFunc.getJsonValueStr("vImage", itemObject));
                    }
                }

                @Override
                public void onDeleteClick(int position, JSONObject itemObject) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("deleteMsg", itemObject)),
                            generalFunc.retrieveLangLBl("", "LBL_NO"),
                            generalFunc.retrieveLangLBl("", "LBL_YES"), button_Id -> {
                                if (button_Id == 1) {
                                    if (intCheck.isNetworkConnected()) {
                                        deletedMedia(itemObject);
                                    } else {
                                        generalFunc.showMessage(binding.categoryHTxt, generalFunc.retrieveLangLBl("", "LBL_NO_INTERNET_TXT"));
                                    }
                                }
                            });
                }
            });
            binding.bidAdditionalMediaRV.setAdapter(mediaAdapter);

            deleteAllMedia();
            //getMedia();
        } else {
            binding.additionalMediaArea.setVisibility(View.GONE);
        }
    }

    @SuppressLint("SetTextI18n")
    private void setLabel() {
        binding.categoryHTxt.setText(getIntent().getStringExtra("SelectvVehicleType"));

        binding.locationHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_SERVICE_ADDRESS_TXT"));
        binding.locationTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_SELECT_LOCATION_TXT"));
        addToClickHandler(binding.locationTxt);

        binding.budgetHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_BUDGET_TXT"));
        binding.budgetBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_AMOUNT"));
        binding.budgetBox.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        binding.budgetBox.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        binding.budgetBox.addTextChangedListener(null, false);

        binding.budgetCurrency.setText(generalFunc.getJsonValueStr("vCurrencyPassenger", obj_userProfile));
        if (generalFunc.isRTLmode()) {
            binding.budgetCurrency.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.right_radius_rtl));
        }

        binding.taskDetailsMsgBox.setHint(generalFunc.retrieveLangLBl("", "LBL_BIDDING_DETAILS_TXT"));
        MyUtils.editBoxMultiLine(binding.taskDetailsMsgBox);

        binding.dateTimeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DATE_TIME_TXT"));
        binding.dateTimeEditBox.setHint(generalFunc.retrieveLangLBl("", "LBL_SELECT_DATE_TIME_HINT"));
        addToClickHandler(binding.dateTimeEditBox);

        binding.additionalMediaHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_MEDIA_TXT"));

        bidCancelBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        bidPostBtn.setText(generalFunc.retrieveLangLBl("", "LBL_POST_BID_TXT"));
        if (generalFunc.getJsonValueStr("ENABLE_TAX_FOR_BIDDING_SERVICE", obj_userProfile).equalsIgnoreCase("yes")) {
            binding.bidAdditionalNote.setVisibility(View.VISIBLE);
            binding.bidAdditionalNote.setText(generalFunc.retrieveLangLBl("", "LBL_TAX_WILL_APPLY_TAXI_BID_TEXT"));
        } else {
            binding.bidAdditionalNote.setVisibility(View.GONE);
        }
    }

    private void openMediaSelectedDialog() {
        AlertDialog.Builder alertBuilder = new AlertDialog.Builder(getActContext());
        DialogMediaSelectedBinding binding = DialogMediaSelectedBinding.inflate(LayoutInflater.from(getActContext()));
        if (generalFunc.isRTLmode()) {
            binding.imageArrow.setRotation(90);
            binding.videoArrow.setRotation(90);
            binding.documentArrow.setRotation(90);
            binding.audioArrow.setRotation(90);
        }
        binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_MEDIA_TXT"));
        binding.imageHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_IMAGE"));
        binding.videoHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIDEO"));
        binding.documentHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DOCUMET"));
        binding.audioHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AUDIO_FILE"));

        alertBuilder.setView(binding.getRoot());
        alertBuilder.setCancelable(false);

        AlertDialog alertDialog = alertBuilder.create();

        binding.closeImg.setOnClickListener(v -> alertDialog.dismiss());
        binding.imageArea.setOnClickListener(v -> {
            getFileSelector().openFileSelection(FileSelector.FileType.Image);
            alertDialog.dismiss();
        });
        binding.videoArea.setOnClickListener(v -> {
            getFileSelector().openFileSelection(FileSelector.FileType.Video);
            alertDialog.dismiss();
        });
        binding.documentArea.setOnClickListener(v -> {
            getFileSelector().openFileSelection(FileSelector.FileType.Document);
            alertDialog.dismiss();
        });
        binding.audioArea.setOnClickListener(v -> {
            getFileSelector().openFileSelection(FileSelector.FileType.Audio);
            alertDialog.dismiss();
        });

        alertDialog.setCancelable(false);
        alertDialog.getWindow().setBackgroundDrawable(new ColorDrawable(android.graphics.Color.TRANSPARENT));
        LayoutDirection.setLayoutDirection(alertDialog);
        alertDialog.show();
    }

    private void addViewAdd() {
        ibiddingPostMediaId = "";
        while (mediaListArray.length() > 0) {
            mediaListArray.remove(0);
        }
        try {
            JSONObject jsonObject = new JSONObject();
            jsonObject.put("Add", "Add");
            mediaListArray.put(jsonObject);
        } catch (JSONException e) {
            throw new RuntimeException(e);
        }
    }

    private Context getActContext() {
        return RequestBidInfoActivity.this;
    }

    private Activity getAct() {
        return RequestBidInfoActivity.this;
    }

    private void postBidTask(boolean isOutStandingAdjusted) {
        if (!Utils.checkText(Utils.getText(binding.locationTxt)) ||
                !Utils.checkText(Utils.getText(binding.budgetBox)) ||
                !Utils.checkText(Utils.getText(binding.dateTimeEditBox))) {
            generalFunc.showMessage(binding.categoryHTxt, generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));
            return;
        }
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "PostBid");
        parameters.put("iBiddingId", getIntent().getStringExtra("SelectedVehicleTypeId"));
        parameters.put("tDescription", Utils.getText(binding.taskDetailsMsgBox));
        parameters.put("fBiddingAmount", binding.budgetBox.getTxt());
        parameters.put("dBiddingDate", Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, selectedDate));
        parameters.put("iAddressId", iUserAddressId);

        parameters.put("ibiddingPostMediaId", ibiddingPostMediaId);
        if (isOutStandingAdjusted) {
            parameters.put("isAddOutstandingAmt", "Yes");
            parameters.put("isOutStandingAdjusted", "Yes");
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                String message_str = generalFunc.getJsonValue(Utils.message_str, responseString);
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    /*SuccessDialog.showSuccessDialog(getActContext(),
                            generalFunc.retrieveLangLBl("", "LBL_BIDDING_POSTED"),
                            generalFunc.retrieveLangLBl("", message_str),
                            generalFunc.retrieveLangLBl("", "LBL_VIEW_BIDDING_TASK_TEXT"),
                            generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), false, () -> {
                                Bundle bn = new Bundle();
                                bn.putBoolean("isrestart", true);
                                bn.putBoolean("isBid", true);
                                bn.putString("selType", Utils.CabGeneralType_UberX);
                                new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
                                finish();
                            }, () -> {
                                Bundle bn = new Bundle();
                                new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                                finishAffinity();
                            });*/

                    CustomDialog customDialog = new CustomDialog(this, generalFunc);
                    customDialog.setDetails(
                            generalFunc.retrieveLangLBl("", "LBL_BIDDING_POSTED"),
                            generalFunc.retrieveLangLBl("", message_str),
                            generalFunc.retrieveLangLBl("", "LBL_VIEW_BIDDING_TASK_TEXT"),
                            generalFunc.retrieveLangLBl("Ok", "LBL_OK"),
                            false,
                            R.drawable.ic_correct_2,
                            false,
                            1,
                            true);
                    customDialog.createDialog();
                    customDialog.setPositiveButtonClick(() -> {
                        Bundle bn = new Bundle();
                        bn.putBoolean("isrestart", true);
                        bn.putBoolean("isBid", true);
                        bn.putString("selType", Utils.CabGeneralType_UberX);
                        new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
                        finish();
                    });
                    customDialog.setNegativeButtonClick(() -> {
                        Bundle bn = new Bundle();
                        new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                        finishAffinity();
                    });
                    customDialog.show();


                } else {
                    String fOutStandingAmount = generalFunc.getJsonValue("fOutStandingAmount", responseString);
                    if (GeneralFunctions.parseDoubleValue(0.0, fOutStandingAmount) > 0) {
                        outstandingDialog(responseString);
                        return;
                    }
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message_str));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void outstandingDialog(String responseString) {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dailog_outstanding, null);
        final MTextView outStandingTitle = (MTextView) dialogView.findViewById(R.id.outStandingTitle);
        final MTextView outStandingValue = (MTextView) dialogView.findViewById(R.id.outStandingValue);
        final MTextView cardtitleTxt = (MTextView) dialogView.findViewById(R.id.cardtitleTxt);
        final MTextView adjustTitleTxt = (MTextView) dialogView.findViewById(R.id.adjustTitleTxt);
        final LinearLayout cardArea = (LinearLayout) dialogView.findViewById(R.id.cardArea);
        final LinearLayout adjustarea = (LinearLayout) dialogView.findViewById(R.id.adjustarea);
        final MTextView adjustSubTitleTxt = dialogView.findViewById(R.id.adjustSubTitleTxt);
        final MTextView adjustTripMessageTxt = dialogView.findViewById(R.id.adjustTripMessageTxt);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.imgCardPayNow)).setRotationY(180);
            (dialogView.findViewById(R.id.imgAdjustInTrip)).setRotationY(180);
        }

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        outStandingTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUTSTANDING_AMOUNT_TXT"));
        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in your Task", "LBL_ADJUST_OUT_AMT_TASK_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));
        outStandingValue.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("fOutStandingAmountWithSymbol", responseString)));
        cardtitleTxt.setText(generalFunc.retrieveLangLBl("Pay Now", "LBL_PAY_NOW"));

        ShowAdjustTripBtn = generalFunc.getJsonValue("ShowAdjustTripBtn", responseString);
        ShowAdjustTripBtn = (ShowAdjustTripBtn == null || ShowAdjustTripBtn.isEmpty()) ? "No" : ShowAdjustTripBtn;
        ShowPayNow = generalFunc.getJsonValue("ShowPayNow", responseString);
        ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", responseString);
        ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;
        ShowPayNow = (ShowPayNow == null || ShowPayNow.isEmpty()) ? "No" : ShowPayNow;

        if (ShowPayNow.equalsIgnoreCase("Yes") && ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.VISIBLE);
        } else if (ShowPayNow.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        } else if (ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                adjustTripMessageTxt.setText(outstanding_restriction_label);
            }
        }

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(ContactUsActivity.class));
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            Bundle bn = new Bundle();
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", obj_userProfile) + "&eType=" + "Bidding" + "&ePaymentType=ChargeOutstandingAmount";
            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();
            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
        });

        adjustarea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            postBidTask(true);
        });

        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setOnClickListener(v -> outstanding_dialog.dismiss());
        builder.setView(dialogView);
        outstanding_dialog = builder.create();
        LayoutDirection.setLayoutDirection(outstanding_dialog);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.cardimagearrow)).setRotationY(180);
            (dialogView.findViewById(R.id.adjustimagearrow)).setRotationY(180);
        }
        outstanding_dialog.setCancelable(false);
        outstanding_dialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == R.id.backImgView || i == bidCancelBtn.getId()) {
            onBackPressed();
        } else if (i == binding.locationTxt.getId()) {

            Intent intent = new Intent(getActContext(), ListAddressActivity.class);
            Bundle bn = new Bundle();
            bn.putBoolean("isBid", true);
            bn.putString("iUserAddressId", iUserAddressId);
            intent.putExtras(bn);
            launchActivity.launch(intent);

        } else if (i == binding.dateTimeEditBox.getId()) {

            Calendar cal = Calendar.getInstance(MyUtils.getLocale());
            if (selectedDate != null) {
                cal.setTime(selectedDate);
            } else {
                cal.add(Calendar.MINUTE, (Integer.parseInt(generalFunc.getJsonValueStr("MINIMUM_HOURS_LATER_BIDDING", obj_userProfile)) * 60) + 1);
            }
            bottomScheduleDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_SCHEDULE")
                    , generalFunc.retrieveLangLBl("", "LBL_SET"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"),
                    "", true, cal);

        } else if (i == bidPostBtn.getId()) {
            postBidTask(false);
        }
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    iUserAddressId = data.getStringExtra("addressId");
                    binding.locationTxt.setText(data.getStringExtra("address"));
                }
            });

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {
            //postBidTask(false);
        }
    }

    private void deleteAllMedia() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UploadBiddingMedia");
        parameters.put("action_type", "DELETEALL");

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            //
        });
    }

    private void getMedia() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UploadBiddingMedia");
        parameters.put("action_type", "GET");

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            if (Utils.checkText(responseString)) {
                Log.i("UploadBiddingMedia", "getMedia: " + responseString.toString());
                addViewAdd();
                JSONArray array = generalFunc.getJsonArray("BiddingPostMedia", responseString);
                if (array != null) {
                    for (int i = 0; i < array.length(); i++) {
                        JSONObject tempObj = generalFunc.getJsonObject(array, i);
                        mediaListArray.put(tempObj);

                        if (ibiddingPostMediaId.equalsIgnoreCase("")) {
                            ibiddingPostMediaId = generalFunc.getJsonValueStr("ibiddingPostMediaId", tempObj);
                        } else {
                            ibiddingPostMediaId = ibiddingPostMediaId + "," + generalFunc.getJsonValueStr("ibiddingPostMediaId", tempObj);
                        }
                    }
                }
                mediaAdapter.updateData(mediaListArray);
            } else {
                generalFunc.showError();
            }
        });
    }

    private void deletedMedia(JSONObject itemObject) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UploadBiddingMedia");
        parameters.put("action_type", "DELETE");
        parameters.put("ibiddingPostMediaId", generalFunc.getJsonValueStr("ibiddingPostMediaId", itemObject));
        parameters.put("eMediaType", generalFunc.getJsonValueStr("eMediaType", itemObject));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {

                getMedia();
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showMessage(binding.categoryHTxt, generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        super.onFileSelected(mFileUri, mFilePath, mFileType);

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "UploadBiddingMedia");
            put("action_type", "ADD");
        }};

        String temp_File_Name = "", showMsg = "";
        if (mFileType != FileSelector.FileType.Image) {
            temp_File_Name = Utils.getFileExt(mFilePath);
        }

        if (mFileType == FileSelector.FileType.Image) {
            paramsList.put("eMediaType", "Image");
            temp_File_Name = Utils.TempProfileImageName;
            showMsg = generalFunc.retrieveLangLBl("", "LBL_IMAGE_UPLOADING");

        } else if (mFileType == FileSelector.FileType.Video) {
            paramsList.put("eMediaType", "Video");
            temp_File_Name = "temp_video." + temp_File_Name;
            showMsg = generalFunc.retrieveLangLBl("", "LBL_VIDEO_UPLOADING");

        } else if (mFileType == FileSelector.FileType.Document) {
            paramsList.put("eMediaType", "Document");
            temp_File_Name = "temp_document." + temp_File_Name;
            showMsg = generalFunc.retrieveLangLBl("", "LBL_DOCUMET_UPLOADING");

        } else if (mFileType == FileSelector.FileType.Audio) {
            paramsList.put("eMediaType", "Audio");
            temp_File_Name = "temp_audio." + temp_File_Name;
            showMsg = generalFunc.retrieveLangLBl("", "LBL_AUDIO_UPLOADING");
        }

        new UploadProfileImage(getAct(), mFilePath, temp_File_Name, paramsList).execute(true, showMsg);
    }

    public void handleImgUploadResponse(String responseString) {
        if (Utils.checkText(responseString)) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                getMedia();
                generalFunc.showMessage(binding.categoryHTxt, generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            }
        } else {
            generalFunc.showError();
        }
    }
}