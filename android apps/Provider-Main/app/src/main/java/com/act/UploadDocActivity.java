package com.act;

import android.app.Activity;
import android.content.Context;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.MotionEvent;
import android.view.View;

import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.DatePicker;
import com.general.files.ActUtils;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.UploadProfileImage;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityUploadDocBinding;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Locale;

public class UploadDocActivity extends ParentActivity {


    private MButton btn_type2;


    private String selectedDocumentPath = "", vImage = "";
    private boolean isUploadImageNew = true, isBtnClick = false;


    private String SELECTED_DATE = "";
    private ActivityUploadDocBinding binding;

    @Override
    public void finishActivity(int requestCode) {
        super.finishActivity(requestCode);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_upload_doc);
        setSupportActionBar(binding.toolbarInclude.toolbar);

        if (!getIntent().getStringExtra("vimage").equalsIgnoreCase("")) {
            vImage = getIntent().getStringExtra("vimage");
        }
        addToClickHandler(binding.editTxtView);
        addToClickHandler(binding.viewTxtView);

        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        addToClickHandler(binding.toolbarInclude.backImgView);
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }

        btn_type2.setId(Utils.generateViewId());
        addToClickHandler(btn_type2);
        addToClickHandler(binding.helpInfoTxtView);
        addToClickHandler(binding.dummyInfoCardImgView);
        setLabels();
        SimpleDateFormat date_format = new SimpleDateFormat(DateTimeUtils.DayFormatEN, Locale.US);
        SELECTED_DATE = date_format.format(Calendar.getInstance(MyUtils.getLocale()).getTime());


        if (getIntent().getStringExtra("allow_date_change").equalsIgnoreCase("No")) {
            btn_type2.setEnabled(false);
            ((MTextView) binding.noteTxt).setText(getIntent().getStringExtra("doc_update_disable"));
        }
    }

    private void setLabels() {
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC"));
        binding.editTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_EDIT"));
        binding.viewTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));
        binding.helpInfoTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_DOC"));
        binding.expBox.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT"));
        binding.expBoxLBL.setText(generalFunc.retrieveLangLBl("", "LBL_EXPIRY_DATE"));

        if (getIntent().getStringExtra("ex_status").equals("yes")) {
            if (Utils.checkText(getIntent().getStringExtra("ex_date"))) {
                binding.expBoxTxt.setText(generalFunc.getDateFormatedType(getIntent().getStringExtra("ex_date"), DateTimeUtils.DayFormatEN, DateTimeUtils.WithoutDayFormat));
            }
            /*if (Utils.checkText(getIntent().getStringExtra("tDisplayDate"))) {
                binding.expBoxTxt.setText(getIntent().getStringExtra("tDisplayDate"));
            }*/ else {
                binding.expBoxTxt.setVisibility(View.GONE);
            }
            binding.expDateSelectArea.setVisibility(View.VISIBLE);
        } else {
            binding.expDateSelectArea.setVisibility(View.GONE);
        }

        String doc_file = getIntent().getStringExtra("doc_file");
        if (!doc_file.equals("")) {
            selectedDocumentPath = doc_file;
            binding.imgeselectview.setVisibility(View.VISIBLE);
            binding.helpInfoTxtView.setVisibility(View.GONE);
            binding.editArea.setVisibility(View.VISIBLE);

            if (!vImage.equalsIgnoreCase("")) {
                binding.viewTxtView.setVisibility(View.VISIBLE);
                binding.editView.setVisibility(View.VISIBLE);

            } else {
                binding.viewTxtView.setVisibility(View.GONE);
                binding.editView.setVisibility(View.GONE);
            }
            binding.dummyInfoCardImgView.setAlpha(0.2f);
            binding.dummyInfoCardImgView.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_card_documents));
            isUploadImageNew = false;
        }

        binding.selectyearLayout.setOnTouchListener(new setOnTouchList());
        addToClickHandler(binding.selectyearLayout);
    }

    private static class setOnTouchList implements View.OnTouchListener {

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {
            if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view.hasFocus()) {
                view.performClick();
            }
            return true;
        }
    }

    private Context getActContext() {
        return UploadDocActivity.this;
    }

    private void checkData() {

        if (selectedDocumentPath.equals("")) {
            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("Please attach your document.", "LBL_SELECT_DOC_ERROR"));
            return;
        }
        if (binding.expDateSelectArea.getVisibility() == View.VISIBLE && !Utils.checkText(binding.expBoxTxt.getText().toString())) {
            generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("Expiry date is required.", "LBL_EXP_DATE_REQUIRED"));
            return;
        }

        if (isBtnClick) {
            return;
        }
        isBtnClick = true;
        new Handler(Looper.myLooper()).postDelayed(() -> isBtnClick = false, 1000);

        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "uploaddrivedocument");
            put("iMemberId", generalFunc.getMemberId());
            put("MemberType", Utils.app_type);
            put("doc_usertype", getIntent().getStringExtra("PAGE_TYPE"));
            put("doc_masterid", getIntent().getStringExtra("doc_masterid"));
            put("doc_name", getIntent().getStringExtra("doc_name"));
            put("doc_id", getIntent().getStringExtra("doc_id"));
            put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            put("GeneralUserType", Utils.app_type);
            put("GeneralMemberId", generalFunc.getMemberId());
            put("ex_date", getIntent().getStringExtra("ex_status").equals("yes") ? generalFunc.getDateFormatedType(Utils.getText(binding.expBoxTxt), DateTimeUtils.WithoutDayFormat, DateTimeUtils.DayFormatEN, Locale.US) : "");

            if (!getIntent().getStringExtra("iDriverVehicleId").equals("")) {
                put("iDriverVehicleId", getIntent().getStringExtra("iDriverVehicleId"));
            }
        }};

        UploadProfileImage uploadProfileImage;
        if (!getIntent().getStringExtra("doc_file").equals("")) {

            if (isUploadImageNew) {
                uploadProfileImage = new UploadProfileImage(UploadDocActivity.this, selectedDocumentPath, "TempFile." + Utils.getFileExt(selectedDocumentPath), paramsList, "FILE");
                uploadProfileImage.execute(true, generalFunc.retrieveLangLBl("", "LBL_DOCUMET_UPLOADING"));
            } else {
                paramsList.put("doc_file", selectedDocumentPath);
                uploadProfileImage = new UploadProfileImage(UploadDocActivity.this, "", "TempFile." + Utils.getFileExt(selectedDocumentPath), paramsList, "FILE");
                uploadProfileImage.execute(false, generalFunc.retrieveLangLBl("", "LBL_DOCUMET_UPLOADING"));
            }
        } else {
            uploadProfileImage = new UploadProfileImage(UploadDocActivity.this, selectedDocumentPath, "TempFile." + Utils.getFileExt(selectedDocumentPath), paramsList, "FILE");
            uploadProfileImage.execute(true, generalFunc.retrieveLangLBl("", "LBL_DOCUMET_UPLOADING"));
        }

    }

    public void handleImgUploadResponse(String responseString) {

        if (responseString != null && !responseString.equals("")) {

            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                String msgTxt;
                if (!generalFunc.getJsonValue("doc_under_review", responseString).equalsIgnoreCase("")) {
                    msgTxt = generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("doc_under_review", responseString));
                } else {
                    msgTxt = generalFunc.retrieveLangLBl("Your document is uploaded successfully", "LBL_UPLOAD_DOC_SUCCESS");
                }

                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    generateAlert.closeAlertBox();
                    setResult(RESULT_OK);
                    binding.toolbarInclude.backImgView.performClick();
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

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {

        this.selectedDocumentPath = mFilePath;
        binding.imgeselectview.setVisibility(View.VISIBLE);
        binding.dummyInfoCardImgView.setAlpha(0.2f);
        binding.dummyInfoCardImgView.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_card_documents));
        isUploadImageNew = true;

        binding.helpInfoTxtView.setVisibility(View.GONE);
        binding.editArea.setVisibility(View.VISIBLE);

        if (!vImage.equalsIgnoreCase("")) {
            binding.viewTxtView.setVisibility(View.VISIBLE);
            binding.editView.setVisibility(View.VISIBLE);

        } else {
            binding.viewTxtView.setVisibility(View.GONE);
            binding.editView.setVisibility(View.GONE);
        }
    }

    private void openDocChoose() {
        getFileSelector().openFileSelection(FileSelector.FileType.Document);
    }

    private void openCalender() {
        DatePicker.show(getActContext(), generalFunc, Calendar.getInstance(), null, SELECTED_DATE, null, (year, monthOfYear, dayOfMonth) -> {
            SimpleDateFormat date_format1 = new SimpleDateFormat(DateTimeUtils.DayFormatEN, Locale.US);
            try {
                Date cal = date_format1.parse(year + "-" + monthOfYear + "-" + dayOfMonth);
                if (cal != null) {
                    SELECTED_DATE = date_format1.format(cal.getTime());
                    binding.expBoxTxt.setVisibility(View.VISIBLE);
                    binding.expBoxTxt.setText(Utils.convertDateToFormat(DateTimeUtils.DateFormat, cal));
                }

            } catch (ParseException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        });
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(UploadDocActivity.this);
        if (i == binding.toolbarInclude.backImgView.getId()) {
            UploadDocActivity.super.onBackPressed();
        } else if (i == btn_type2.getId()) {
            checkData();
        } else if (i == binding.helpInfoTxtView.getId() || i == binding.dummyInfoCardImgView.getId()) {
            openDocChoose();
        } else if (i == binding.selectyearLayout.getId()) {
            openCalender();
        } else if (i == binding.editTxtView.getId()) {
            openDocChoose();
        } else if (i == binding.viewTxtView.getId()) {
            new ActUtils(getActContext()).openURL(vImage);
        }
    }

}