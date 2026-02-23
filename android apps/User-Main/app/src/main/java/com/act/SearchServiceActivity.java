package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.UberXSearchCategoryAdapter;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Utils;
import com.view.MTextView;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class SearchServiceActivity extends ParentActivity {

    private ImageView imageCancel;
    private EditText searchTxtView;
    private LinearLayout noDataArea;
    private AVLoadingIndicatorView loaderView;

    private ServerTask currentCallExeWebServer;
    private String mSearchText = "";

    private UberXSearchCategoryAdapter mUfxSearchCatAdapter;
    private ArrayList<HashMap<String, String>> mSearchList;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_search_service);

        initView();
        searchTxtView.setHint(generalFunc.retrieveLangLBl("", "LBL_SEARCH_SERVICES"));
    }

    private void initView() {
        ImageView backImgView = findViewById(R.id.backImgView);
        searchTxtView = findViewById(R.id.searchTxtView);
        RecyclerView rvSearchCategory = findViewById(R.id.rvSearchCategory);
        imageCancel = findViewById(R.id.imageCancel);
        imageCancel.setVisibility(View.GONE);
        addToClickHandler(imageCancel);

        loaderView = findViewById(R.id.loaderView);
        loaderView.setVisibility(View.GONE);

        MTextView noPlaceData = findViewById(R.id.noPlacedata);
        noPlaceData.setText(generalFunc.retrieveLangLBl("", "LBL_SEARCH_RESULT"));

        mSearchList = new ArrayList<>();

        setSearchTextWatcher();

        noDataArea = findViewById(R.id.noDataArea);
        noDataArea.setVisibility(View.GONE);

        RelativeLayout mainArea = findViewById(R.id.mainArea);
        Animation a = AnimationUtils.loadAnimation(this, R.anim.slide_from_right);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
            a = AnimationUtils.loadAnimation(this, R.anim.slide_from_left);
        }
        a.reset();
        mainArea.clearAnimation();
        mainArea.startAnimation(a);
        addToClickHandler(backImgView);

        mUfxSearchCatAdapter = new UberXSearchCategoryAdapter(getActContext(), mSearchList, generalFunc);
        rvSearchCategory.setAdapter(mUfxSearchCatAdapter);
        mUfxSearchCatAdapter.setOnItemClickList(new UberXSearchCategoryAdapter.OnItemClickList() {
            @Override
            public void onItemClick(int position) {
                HashMap<String, String> selectedItem = mSearchList.get(position);
                Intent intent = new Intent();
                //intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
                intent.putExtra("selectedItem", selectedItem);
                intent.putExtra("eShowTerms", mSearchList.get(position).get("eShowTerms"));
                setResult(RESULT_OK, intent);
                finish();
            }

            @Override
            public void onMultiItem(String id, boolean b) {

            }
        });
        Utils.showSoftKeyboard(this, searchTxtView);
    }

    private void setSearchTextWatcher() {

        searchTxtView.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
            }

            @Override
            public void afterTextChanged(Editable s) {
                mSearchText = s.toString();
                if (mSearchText.length() >= 3) {
                    searchCategory(mSearchText);
                } else {
                    if (mSearchText.length() == 0) {
                        imageCancel.setVisibility(View.GONE);
                        searchCategory("");
                    } else {
                        imageCancel.setVisibility(View.VISIBLE);
                    }
                    mSearchList.clear();
                    mUfxSearchCatAdapter.notifyDataSetChanged();
                    noDataArea.setVisibility(View.GONE);
                    loaderView.setVisibility(View.GONE);
                }
            }
        });
    }

    private void searchCategory(String s) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "SearchService");
        parameters.put("search_keyword", s);
        imageCancel.setVisibility(View.GONE);
        loaderView.setVisibility(View.VISIBLE);

        if (currentCallExeWebServer != null) {
            currentCallExeWebServer.cancel(true);
            currentCallExeWebServer = null;
        }

        currentCallExeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {

            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            loaderView.setVisibility(View.GONE);

            if (responseObj != null && !responseObj.toString().equals("")) {
                mSearchList.clear();
                noDataArea.setVisibility(View.GONE);
                imageCancel.setVisibility(View.VISIBLE);
                loaderView.setVisibility(View.GONE);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    JSONArray mainCataArray = generalFunc.getJsonArray("message", responseObj);
                    int mainCatArraySize = mainCataArray.length();
                    for (int i = 0; i < mainCatArraySize; i++) {
                        HashMap<String, String> map = new HashMap<>();
                        JSONObject categoryObj = generalFunc.getJsonObject(mainCataArray, i);
                        map.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", categoryObj));
                        map.put("vCategory", generalFunc.getJsonValueStr("vCategory", categoryObj));
                        map.put("eCatType", generalFunc.getJsonValueStr("eCatType", categoryObj));
                        map.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", categoryObj));
                        map.put("vListLogo2", generalFunc.getJsonValueStr("vListLogo2", categoryObj));
                        map.put("iParentId", generalFunc.getJsonValueStr("iParentId", categoryObj));
                        map.put("vLogo", generalFunc.getJsonValueStr("vLogo", categoryObj));
                        map.put("eShowTerms", generalFunc.getJsonValueStr("eShowTerms", categoryObj));
                        map.put("iBiddingId", generalFunc.getJsonValueStr("iBiddingId", categoryObj));
                        map.put("other", generalFunc.getJsonValueStr("other", categoryObj));
                        if (categoryObj.has("eDeliveryType")) {
                            map.put("eDeliveryType", generalFunc.getJsonValueStr("eDeliveryType", categoryObj));
                        }
                        mSearchList.add(map);
                    }
                } else {
                    if (mSearchText.length() >= 3) {
                        noDataArea.setVisibility(View.VISIBLE);
                    }
                }
                if (mSearchText.length() <= 2) {
                    mSearchList.clear();
                }
                mUfxSearchCatAdapter.notifyDataSetChanged();
            } else {
                generalFunc.showError();
            }
        });

    }

    public void onClick(View v) {
        Utils.hideKeyboard(getActContext());
        int id = v.getId();
        if (id == R.id.backImgView) {
            finish();
        } else if (id == R.id.imageCancel) {
            searchTxtView.setText("");
            mSearchList.clear();
            mUfxSearchCatAdapter.notifyDataSetChanged();
            noDataArea.setVisibility(View.GONE);
        }
    }

    private Context getActContext() {
        return SearchServiceActivity.this;
    }
}