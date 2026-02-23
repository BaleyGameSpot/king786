package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.UberXSubCategory22Adapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class UberxFilterActivity extends ParentActivity implements UberXSubCategory22Adapter.OnItemClickList {

    ImageView backImgView;
    RecyclerView dataListRecyclerView;
    UberXSubCategory22Adapter ufxCatAdapter;
    private final ArrayList<HashMap<String, String>> generalCategoryList = new ArrayList<>();
    private final ArrayList<String> multiServiceSelect = new ArrayList<>();

    private int submitBtnId;
    private String SelectedVehicleTypeId = "";
    MTextView selectServiceTxt;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_uberx_filter);

        MTextView titleTxt = findViewById(R.id.titleTxt);
        selectServiceTxt = findViewById(R.id.selectServiceTxt);
        backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        dataListRecyclerView = findViewById(R.id.dataListRecyclerView);

        ufxCatAdapter = new UberXSubCategory22Adapter(getActContext(), generalCategoryList, generalFunc);
        dataListRecyclerView.setAdapter(ufxCatAdapter);
        ufxCatAdapter.setOnItemClickList(this);

        MButton btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_FILTER_TXT"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_SERVICE"));
        SelectedVehicleTypeId = getIntent().getStringExtra("SelectedVehicleTypeId");

        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);
        getCategory();
    }

    private void getCategory() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategories");
        parameters.put("parentId", getIntent().getStringExtra("parentId"));
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("eForVideoConsultation", getIntent().getStringExtra("eForVideoConsultation"));

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            generalCategoryList.clear();
            if (responseObj != null && !responseObj.toString().equals("")) {
                selectServiceTxt.setText(generalFunc.getJsonValueStr("vParentCategoryName", responseObj));
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    generalCategoryList.clear();

                    JSONArray mainCataArray = generalFunc.getJsonArray("message", responseObj);

                    if (mainCataArray != null) {
                        String LBL_BOOK_NOW = generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW");
                        for (int i = 0; i < mainCataArray.length(); i++) {
                            HashMap<String, String> map = new HashMap<>();
                            JSONObject categoryObj = generalFunc.getJsonObject(mainCataArray, i);
                            map.put("eCatType", generalFunc.getJsonValueStr("eCatType", categoryObj));
                            map.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", categoryObj));
                            map.put("vCategory", generalFunc.getJsonValueStr("vCategory", categoryObj));
                            map.put("vLogo_image", generalFunc.getJsonValueStr("vLogo_image", categoryObj));
                            map.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", categoryObj));
                            map.put("vCategoryBanner", generalFunc.getJsonValueStr("vCategoryBanner", categoryObj));
                            map.put("vBannerImage", generalFunc.getJsonValueStr("vBannerImage", categoryObj));
                            map.put("tBannerButtonText", generalFunc.getJsonValueStr("tBannerButtonText", categoryObj));
                            String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", categoryObj);
                            map.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
                            map.put("LBL_BOOK_NOW", LBL_BOOK_NOW);

                            map.put("VideoConsultSection", getIntent().getStringExtra("eForVideoConsultation"));

                            if (SelectedVehicleTypeId.contains(generalFunc.getJsonValueStr("iVehicleCategoryId", categoryObj))) {
                                map.put("isCheck", "Yes");
                            } else {
                                map.put("isCheck", "No");
                            }
                            generalCategoryList.add(map);
                        }
                    }
                    ufxCatAdapter.updateList(generalCategoryList);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message", responseObj)));
                }
            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }

    private Context getActContext() {
        return UberxFilterActivity.this;
    }

    @Override
    public void onItemClick(int position) {

    }

    @Override
    public void onMultiItem(String id, String category, boolean b) {
        if (multiServiceSelect.contains(id)) {
            if (!b) {
                while (multiServiceSelect.remove(id)) {
                }
            }
        } else {
            multiServiceSelect.add(id);
        }
    }


    public void onClick(View view) {
        Utils.hideKeyboard(UberxFilterActivity.this);
        int i = view.getId();
        if (i == backImgView.getId()) {
            onBackPressed();
        } else if (i == submitBtnId) {
            Bundle bn = new Bundle();
            if (multiServiceSelect.size() > 0) {
                bn.putString("SelectedVehicleTypeId", android.text.TextUtils.join(",", multiServiceSelect));
                (new ActUtils(getActContext())).setOkResult(bn);
                finish();
            } else {
                generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("Please Select Service", "LBL_SELECT_SERVICE_TXT"));
            }
        }
    }


    @Override
    public void onBackPressed() {
        Bundle bn = new Bundle();
        bn.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
        (new ActUtils(getActContext())).setOkResult(bn);
        finish();
        super.onBackPressed();
    }
}