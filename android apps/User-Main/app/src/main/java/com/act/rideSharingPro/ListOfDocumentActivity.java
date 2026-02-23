package com.act.rideSharingPro;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.databinding.DataBindingUtil;

import com.act.ContactUsActivity;
import com.act.rideSharingPro.adapter.ListOfDocAdapter;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityListOfDocumentBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class ListOfDocumentActivity extends ParentActivity implements ListOfDocAdapter.OnItemClickListener {

    private ActivityListOfDocumentBinding binding;
    private ListOfDocAdapter listOfDocAdapter;
    private final ArrayList<HashMap<String, String>> list = new ArrayList<>();
    private boolean isBtnClick = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_list_of_document);

        initialization();
        getDocList();
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_DOC"));

        ///
        listOfDocAdapter = new ListOfDocAdapter(generalFunc, list, this);
        binding.listOfDocRV.setAdapter(listOfDocAdapter);
    }

    private Context getActContext() {
        return ListOfDocumentActivity.this;
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getDocList() {
        if (binding.errorView.getVisibility() == View.VISIBLE) {
            binding.errorView.setVisibility(View.GONE);
        }
        if (binding.loading.getVisibility() != View.VISIBLE) {
            binding.loading.setVisibility(View.VISIBLE);
        }

        list.clear();
        listOfDocAdapter.notifyDataSetChanged();

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetUserDocument");

        binding.noDocumentsListTxt.setVisibility(View.GONE);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            closeLoader();
            binding.noDocumentsListTxt.setVisibility(View.GONE);

            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);
            if (responseStringObject != null) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {

                    JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseStringObject);

                    if (arr_rides != null && arr_rides.length() > 0) {
                        for (int i = 0; i < arr_rides.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);

                            HashMap<String, String> map = new HashMap<>();

                            map.put("doc_id", generalFunc.getJsonValueStr("doc_id", obj_temp));
                            map.put("doc_name", generalFunc.getJsonValueStr("doc_name", obj_temp));
                            map.put("doc_masterid", generalFunc.getJsonValueStr("doc_masterid", obj_temp));
                            map.put("ex_date", generalFunc.getJsonValueStr("ex_date", obj_temp));
                            map.put("exp_date", generalFunc.getJsonValueStr("exp_date", obj_temp));
                            map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                            map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_temp));
                            map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));
                            map.put("ex_status", generalFunc.getJsonValueStr("ex_status", obj_temp));
                            map.put("vimage", generalFunc.getJsonValueStr("vimage", obj_temp));
                            map.put("doc_file", generalFunc.getJsonValueStr("doc_file", obj_temp));
                            map.put("DOC_STATUS_TEXT", generalFunc.getJsonValueStr("DOC_STATUS_TEXT", obj_temp));
                            map.put("EXPIRE_DOCUMENT", generalFunc.getJsonValueStr("EXPIRE_DOCUMENT", obj_temp));
                            map.put("allow_date_change", generalFunc.getJsonValueStr("allow_date_change", obj_temp));
                            map.put("doc_update_disable", generalFunc.getJsonValueStr("doc_update_disable", obj_temp));
                            map.put("LBL_MANAGE", generalFunc.retrieveLangLBl("Manage", "LBL_MANAGE"));
                            map.put("LBL_UPLOAD_DOC", generalFunc.retrieveLangLBl("Upload document", "LBL_UPLOAD_DOC"));
                            map.put("LBL_MISSING_TXT", generalFunc.retrieveLangLBl("Missing", "LBL_MISSING_TXT"));
                            map.put("LBL_EXPIRED_TXT", generalFunc.retrieveLangLBl("Expired", "LBL_EXPIRED_TXT"));

                            map.put("JSON", obj_temp.toString());
                            list.add(map);

                        }
                    }
                    listOfDocAdapter.notifyDataSetChanged();
                } else {

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        if (btn_id == 0) {
                            generateAlert.closeAlertBox();
                            Bundle bn = new Bundle();
                            bn.putBoolean("isListEmpty", false);
                            new ActUtils(getActContext()).setOkResult(bn);
                            finish();
                        } else if (btn_id == 1) {
                            Intent intent = new Intent(getActContext(), ContactUsActivity.class);
                            intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
                            startActivity(intent);
                            finish();

                        }
                    });

                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_ADD_VEHICLES"));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));

                    generateAlert.showAlertBox();

                    binding.noDocumentsListTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    binding.noDocumentsListTxt.setVisibility(View.VISIBLE);

                }
            } else {
                generateErrorView();
            }
        });
    }

    private void closeLoader() {
        if (binding.loading.getVisibility() == View.VISIBLE) {
            binding.loading.setVisibility(View.GONE);
        }
    }

    private void generateErrorView() {
        closeLoader();
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (binding.errorView.getVisibility() != View.VISIBLE) {
            binding.errorView.setVisibility(View.VISIBLE);
        }
        binding.errorView.setOnRetryListener(this::getDocList);
    }

    @Override
    protected void onResume() {
        super.onResume();
        isBtnClick = false;
    }

    @Override
    public void onItemClickList(int position) {

        if (!isBtnClick) {
            isBtnClick = true;

            Intent intent = new Intent(getActContext(), RideUploadDocActivity.class);
            Bundle bn = new Bundle();
            bn.putBoolean("isOnlyShow", false);
            bn.putSerializable("documentDataHashMap", list.get(position));
            intent.putExtras(bn);
            launchActivity.launch(intent);
        }
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        if (result.getResultCode() == Activity.RESULT_OK) {
            getDocList();
        }
    });

    @SuppressLint("NonConstantResourceId")
    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == R.id.backImgView) {
            ListOfDocumentActivity.super.onBackPressed();
        }
    }
}