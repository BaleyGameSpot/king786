package com.act.parking;

import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.DatePicker;
import com.general.files.ActUtils;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.UploadProfileImage;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityParkingUploadDocBinding;
import com.utils.DateTimeUtils;
import com.utils.LoadImageGlide;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.Calendar;
import java.util.HashMap;

public class ParkingUploadDocActivity extends ParentActivity {

    private ActivityParkingUploadDocBinding binding;
    private HashMap<String, String> documentDataHashMap;
    private MButton btnUploadDocument;
    private String SELECTED_DATE = "", selectedDocumentPath = "", vImage = "";
    private boolean isUploadImageNew = true, isBtnClick = false;

    private final Calendar currCalendar = Calendar.getInstance(MyUtils.getLocale());

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_parking_upload_doc);

        documentDataHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("documentDataHashMap");
        if (documentDataHashMap == null) {
            return;
        }

        initViews();
        setData();
    }

    private void initViews() {
        addToClickHandler(binding.closeImg);
        addToClickHandler(binding.chooseFileView);

        binding.subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PLEASE_SELECT_THE_BELOW_DOCUMENT_TO_CONTINUE"));

        binding.verifyDocHTxt.setText(documentDataHashMap.get("doc_name"));
        binding.selectFileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_FILE"));

        binding.expiryDateHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EXPIRY_DATE"));
        binding.dateTimeEditBox.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_SELECT_DATE"));
        addToClickHandler(binding.dateTimeEditBox);

        btnUploadDocument = ((MaterialRippleLayout) binding.btnUploadDocument).getChildView();
        btnUploadDocument.setId(Utils.generateViewId());
        btnUploadDocument.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));
        addToClickHandler(btnUploadDocument);

        addToClickHandler(binding.viewImg);
        addToClickHandler(binding.editDocImg);

    }

    private void setData() {
        vImage = documentDataHashMap.get("doc_file");

        if (Utils.checkText(vImage)) {
            selectedDocumentPath = vImage;
            binding.chooseFileView.setVisibility(View.GONE);
            binding.viewImg.setVisibility(View.VISIBLE);
            binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_EDIT_DOCUMENT"));

            new LoadImageGlide.builder(this, LoadImageGlide.bind(vImage), binding.setImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            isUploadImageNew = false;
        } else {
            binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_YOUR_DOCS"));
            binding.viewImg.setVisibility(View.GONE);
        }

        if (documentDataHashMap.get("ex_status").equalsIgnoreCase("Yes")) {
            SELECTED_DATE = documentDataHashMap.get("ex_date");
            binding.dateTimeEditBox.setText(documentDataHashMap.get("tDisplayDate"));
            binding.expDateSelectArea.setVisibility(View.VISIBLE);
        } else {
            binding.expDateSelectArea.setVisibility(View.GONE);
        }

        if (getIntent().getBooleanExtra("isOnlyShow", false)) {
            binding.editDocImg.setVisibility(View.GONE);
            binding.dateTimeEditBox.setClickable(false);
            btnUploadDocument.parentView.setVisibility(View.GONE);
            binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_VIEW_DOCUMENT"));
        }
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {

        isUploadImageNew = true;
        selectedDocumentPath = mFilePath;
        vImage = mFilePath;
        binding.viewImg.setVisibility(View.VISIBLE);

        new LoadImageGlide.builder(this, LoadImageGlide.bind(mFilePath), binding.setImgView).setErrorImagePath(R.drawable.ic_card_documents).setPlaceholderImagePath(R.drawable.ic_card_documents).build();
    }

    public void handleImgUploadResponse(String responseString) {
        if (responseString != null && !responseString.equalsIgnoreCase("")) {
            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                String msgTxt;
                if (!generalFunc.getJsonValue("doc_under_review", responseString).equalsIgnoreCase("")) {
                    msgTxt = generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("doc_under_review", responseString));
                } else {
                    msgTxt = generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC_SUCCESS");
                }

                final GenerateAlertBox generateAlert = new GenerateAlertBox(this);
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    generateAlert.closeAlertBox();
                    setResult(RESULT_OK);
                    binding.closeImg.performClick();
                });
                generateAlert.setContentMessage("", msgTxt);
                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                generateAlert.showAlertBox();
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            }
        } else {
            generalFunc.showError();
        }
    }

    private void checkData() {

        if (selectedDocumentPath.equalsIgnoreCase("")) {
            generalFunc.showMessage(binding.subTitleTxt, generalFunc.retrieveLangLBl("Please attach your document.", "LBL_SELECT_DOC_ERROR"));
            return;
        }
        if (binding.expDateSelectArea.getVisibility() == View.VISIBLE && !Utils.checkText(binding.dateTimeEditBox.getText().toString())) {
            generalFunc.showMessage(binding.subTitleTxt, generalFunc.retrieveLangLBl("Expiry date is required.", "LBL_EXP_DATE_REQUIRED"));
            return;
        }

        if (isBtnClick) {
            return;
        }
        isBtnClick = true;
        new Handler(Looper.myLooper()).postDelayed(() -> isBtnClick = false, 1000);

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "UploadParkingSpaceDocuments");
            put("iMemberId", generalFunc.getMemberId());
            put("MemberType", Utils.app_type);

            put("doc_masterid", documentDataHashMap.get("doc_masterid"));
            put("doc_id", documentDataHashMap.get("doc_id"));
            put("ex_date", SELECTED_DATE);
        }};
        UploadProfileImage uploadProfileImage;
        if (Utils.checkText(documentDataHashMap.get("doc_file"))) {

            if (isUploadImageNew) {
                uploadProfileImage = new UploadProfileImage(true, this, selectedDocumentPath, "TempFile." + Utils.getFileExt(selectedDocumentPath), paramsList);
                uploadProfileImage.execute(true, generalFunc.retrieveLangLBl("", "LBL_DOCUMET_UPLOADING"));
            } else {
                paramsList.put("doc_file", selectedDocumentPath);
                uploadProfileImage = new UploadProfileImage(true, this, "", "TempFile." + Utils.getFileExt(selectedDocumentPath), paramsList);
                uploadProfileImage.execute(false, generalFunc.retrieveLangLBl("", "LBL_DOCUMET_UPLOADING"));
            }
        } else {
            uploadProfileImage = new UploadProfileImage(true, this, selectedDocumentPath, "TempFile." + Utils.getFileExt(selectedDocumentPath), paramsList);
            uploadProfileImage.execute(true, generalFunc.retrieveLangLBl("", "LBL_DOCUMET_UPLOADING"));
        }

    }

    private void openCalender() {
        DatePicker.show(this, generalFunc, Calendar.getInstance(), null, Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, currCalendar.getTime()), null, (year, monthOfYear, dayOfMonth) -> {

            currCalendar.set(Calendar.YEAR, year);
            currCalendar.set(Calendar.MONTH, monthOfYear - 1);
            currCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);

            SELECTED_DATE = Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, currCalendar.getTime());
            binding.dateTimeEditBox.setText(Utils.convertDateToFormat(DateTimeUtils.DateFormat, currCalendar.getTime()));


        });
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.closeImg.getId()) {
            onBackPressed();
        } else if (i == binding.chooseFileView.getId() || i == binding.editDocImg.getId()) {
            if (getIntent().getBooleanExtra("isOnlyShow", false)) {
                return;
            }
            getFileSelector().openFileSelection(FileSelector.FileType.Document);

        } else if (i == binding.viewImg.getId()) {
            new ActUtils(this).openURL(vImage);

        } else if (i == binding.dateTimeEditBox.getId()) {
            openCalender();
        } else if (i == btnUploadDocument.getId()) {
            checkData();
        }
    }
}