package com.act;

import android.content.Context;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import com.activity.ParentActivity;
import com.adapter.files.OrganizationListItem;
import com.adapter.files.OrganizationPinnedSectionListAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.OnScrollTouchDelegate;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.pinnedListView.PinnedSectionListView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class SelectOrganizationActivity extends ParentActivity implements OrganizationPinnedSectionListAdapter.OrganizationClick, OnScrollTouchDelegate {

    MTextView titleTxt;
    ProgressBar loading;
    ErrorView errorView;
    MTextView noResTxt;
    PinnedSectionListView organization_list;
    ArrayList<OrganizationListItem> items_list;
    ArrayList<OrganizationListItem> temp_items_list;
    //ImageView searchImgView;
    LinearLayout searcharea, ll_pinnedlist, ll_btn_type2;
    String Organizationid, vCompany;
    View toolbarArea;
    MTextView cancelTxt;
    EditText searchTxt;
    ImageView imageCancel;
    MTextView noData;
    MButton btn_type2;

    OrganizationPinnedSectionListAdapter organizationPinnedSectionListAdapter;
    MTextView headingTxt;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_organization_list);

        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        titleTxt = findViewById(R.id.titleTxt);

        noResTxt = findViewById(R.id.noResTxt);
        noData = findViewById(R.id.noData);
        loading = findViewById(R.id.loading);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        errorView = findViewById(R.id.errorView);
        organization_list = findViewById(R.id.organization_list);
        organization_list.onTouchDegateListener(this);
        //searchImgView = findViewById(R.id.searchImgView);
        searcharea = findViewById(R.id.searcharea);
        ll_pinnedlist = (LinearLayout) findViewById(R.id.ll_pinnedlist);
        ll_btn_type2 = (LinearLayout) findViewById(R.id.ll_btn_type2);
        ll_pinnedlist.setVisibility(View.GONE);
        toolbarArea = findViewById(R.id.toolbarArea);
        cancelTxt = findViewById(R.id.cancelTxt);
        searchTxt = findViewById(R.id.searchTxt);
        headingTxt = findViewById(R.id.headingTxt);
        imageCancel = findViewById(R.id.imageCancel);
        //searchImgView.setVisibility(View.VISIBLE);
        //addToClickHandler(searchImgView);
        addToClickHandler(cancelTxt);
        addToClickHandler(imageCancel);
        headingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_ORGANIZATION_LINK_TO"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_SETUP_PROFILE_TXT"));
//        searchTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_SEARCH_ORGANIZATION"));
        searchTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_Search"));

        btn_type2.setId(Utils.generateViewId());
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
        addToClickHandler(btn_type2);

        organization_list.setClipToOutline(true);


        organization_list.setShadowVisible(true);
        organization_list.setFastScrollEnabled(true);
        organization_list.setFastScrollAlwaysVisible(false);
        items_list = new ArrayList<>();
        temp_items_list = new ArrayList<>();
        //searchImgView.setVisibility(View.GONE);

        searchTxt.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence charSequence, int i, int i1, int i2) {

            }

            @Override
            public void onTextChanged(CharSequence charSequence, int i, int i1, int i2) {

                filterOrganization(charSequence.toString());

            }

            @Override
            public void afterTextChanged(Editable editable) {
            }
        });

        addToClickHandler(backImgView);
        getOrganizationList();


    }


    public void getOrganizationList() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DisplayOrganizationList");
        parameters.put("iUserProfileMasterId", getIntent().getStringExtra("iUserProfileMasterId"));

        noResTxt.setVisibility(View.GONE);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            ll_pinnedlist.setVisibility(View.VISIBLE);

            noResTxt.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {

                closeLoader();

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    //searchImgView.setVisibility(View.VISIBLE);
                    items_list.clear();
                    organizationPinnedSectionListAdapter = new OrganizationPinnedSectionListAdapter(getActContext(), items_list);
                    organization_list.setAdapter(organizationPinnedSectionListAdapter);

                    organizationPinnedSectionListAdapter.setOrganizationClickListener(SelectOrganizationActivity.this);
                    items_list.clear();
                    organizationPinnedSectionListAdapter.notifyDataSetChanged();
                    JSONArray subListArr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    for (int j = 0; j < subListArr.length(); j++) {
                        JSONObject subTempJson = generalFunc.getJsonObject(subListArr, j);
                        String vCompany = generalFunc.getJsonValueStr("vCompany", subTempJson);
                        OrganizationListItem organizationListItem = new OrganizationListItem(OrganizationListItem.ITEM, vCompany);
                        organizationListItem.setiOrganizationId(generalFunc.getJsonValueStr("iOrganizationId", subTempJson));
                        organizationListItem.setiUserProfileMasterId(generalFunc.getJsonValueStr("iUserProfileMasterId", subTempJson));
                        items_list.add(organizationListItem);
                        temp_items_list.add(organizationListItem);
                    }
                    if (subListArr == null || subListArr.length() == 0) {
                        noData.setText(generalFunc.retrieveLangLBl("", "LBL_NO_COUNTRY_AVAIL"));
                        noData.setVisibility(View.VISIBLE);
                    } else {
                        noData.setVisibility(View.GONE);
                    }
                    organizationPinnedSectionListAdapter.notifyDataSetChanged();
                } else {
                    noResTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    noResTxt.setVisibility(View.VISIBLE);
                    ll_pinnedlist.setVisibility(View.GONE);
                    ll_btn_type2.setVisibility(View.GONE);
                    headingTxt.setVisibility(View.GONE);
                    //searchImgView.setVisibility(View.GONE);


                }
            } else {
                generateErrorView();
            }
        });

    }

    protected void onSectionAdded(OrganizationListItem section, int sectionPosition) {
        //sections[sectionPosition] = section;
    }


    public Context getActContext() {
        return SelectOrganizationActivity.this;
    }

    @Override
    public void onTouchDelegate() {

    }

    public void closeLoader() {
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
        }
    }


    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(this::getOrganizationList);
    }


    @Override
    public void OrganizationClickList(OrganizationListItem organizationListItem) {
        Organizationid = organizationListItem.getiOrganizationId();
        vCompany = organizationListItem.text;

    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.backImgView) {
            SelectOrganizationActivity.super.onBackPressed();
        } else if (id == R.id.cancelTxt) {
            toolbarArea.setVisibility(View.VISIBLE);
            searcharea.setVisibility(View.GONE);
            filterOrganization("");
            Utils.hideKeyboard(getActContext());
        } else if (id == R.id.imageCancel) {
            searchTxt.setText("");
        }
        if (view.getId() == btn_type2.getId()) {
            if (!Utils.checkText(vCompany) || !Utils.checkText(Organizationid)) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please select organization.", "LBL_SELECT_ORGANIZATION"));
            } else {
                Bundle bn = new Bundle();
                HashMap<String, String> data = new HashMap<>();
                data.put("email", getIntent().getStringExtra("email"));
                data.put("iUserProfileMasterId", getIntent().getStringExtra("iUserProfileMasterId"));
                data.put("iOrganizationId", Organizationid);
                data.put("vCompany", vCompany);
                bn.putSerializable("data", data);
                new ActUtils(getActContext()).startActWithData(MyBusinessProfileActivity.class, bn);


                finish();
            }
        }
    }


    private void filterOrganization(String searchText) {
        ArrayList<OrganizationListItem> items_list_tmp = new ArrayList<>();
        HashMap<Integer, OrganizationListItem> sectionMapData = new HashMap<>();
        ArrayList<OrganizationListItem> items_list_sub_tmp = new ArrayList<>();
        for (int j = 0; j < temp_items_list.size(); j++) {
            String vCompany = temp_items_list.get(j).text;
            String iOrganizationId = temp_items_list.get(j).getiOrganizationId();

            if (searchText.trim().equals("") || vCompany.toUpperCase().startsWith(searchText.trim().toUpperCase(MyUtils.getLocale()))) {
                OrganizationListItem organizationListItem = new OrganizationListItem(OrganizationListItem.ITEM, vCompany);
                organizationListItem.setiOrganizationId(iOrganizationId);
                items_list_sub_tmp.add(organizationListItem);
            }
        }
        if (items_list_sub_tmp.size() > 0) {
            items_list_tmp.addAll(items_list_sub_tmp);
        }
        for (Integer currentKey : sectionMapData.keySet()) {
            onSectionAdded(sectionMapData.get(currentKey), currentKey);
        }
        items_list.clear();
        items_list.addAll(items_list_tmp);
        organizationPinnedSectionListAdapter = new OrganizationPinnedSectionListAdapter(getActContext(), items_list);
        organization_list.setAdapter(organizationPinnedSectionListAdapter);
        organizationPinnedSectionListAdapter.setOrganizationClickListener(SelectOrganizationActivity.this);
        organizationPinnedSectionListAdapter.notifyDataSetChanged();
    }
}