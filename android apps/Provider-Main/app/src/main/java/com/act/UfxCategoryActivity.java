package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.AbsListView;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.annotation.Nullable;

import com.activity.ParentActivity;
import com.adapter.files.CategoryListItem;
import com.adapter.files.PinnedCategorySectionListAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;
import com.view.anim.loader.AVLoadingIndicatorView;
import com.view.pinnedListView.PinnedSectionListView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class UfxCategoryActivity extends ParentActivity implements PinnedCategorySectionListAdapter.CountryClick {

    private ImageView imageCancel;

    ProgressBar loading;
    ErrorView errorView;
    String next_page_str = "";

    ArrayList<CategoryListItem> categoryitems_list;
    PinnedCategorySectionListAdapter pinnedSectionListAdapter;
    PinnedSectionListView category_list;

    boolean mIsLoading = false;
    boolean isNextPageAvailable = false;
    String UBERX_PARENT_CAT_ID = "";
    private MTextView noResTxt;
    private CategoryListItem mCountryListItem;


    View footerListView;
    private LinearLayout searchView;
    private EditText searchTxtView;
    private AVLoadingIndicatorView loaderView;
    private ServerTask currentCallExeWebServer;

    boolean isSearch = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_ufx_category);


        MTextView titleTxt = (MTextView) findViewById(R.id.titleTxt);
        ImageView backImgView = (ImageView) findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        MTextView introTxt = (MTextView) findViewById(R.id.introTxt);
        introTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_SERVICE_INTRO_TXT"));
        noResTxt = (MTextView) findViewById(R.id.noResTxt);

        searchView = (LinearLayout) findViewById(R.id.searchView);
        searchView.setVisibility(View.GONE);
        imageCancel = (ImageView) findViewById(R.id.imageCancel);
        addToClickHandler(imageCancel);
        imageCancel.setVisibility(View.GONE);
        loaderView = (AVLoadingIndicatorView) findViewById(R.id.loaderView);
        loaderView.setVisibility(View.GONE);

        loading = (ProgressBar) findViewById(R.id.loading);
        errorView = (ErrorView) findViewById(R.id.errorView);
        category_list = (PinnedSectionListView) findViewById(R.id.category_list);
        category_list.setShadowVisible(true);
        UBERX_PARENT_CAT_ID = getIntent().getStringExtra("UBERX_PARENT_CAT_ID");


        searchTxtView = (EditText) findViewById(R.id.searchTxtView);
        searchTxtView.setHint(generalFunc.retrieveLangLBl("", "LBL_SEARCH_SERVICES"));
        searchTxtView.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
            }

            @Override
            public void afterTextChanged(Editable s) {
                if (s.length() == 0) {
                    isSearch = false;
                    imageCancel.setVisibility(View.GONE);
                    loaderView.setVisibility(View.GONE);
                    loaderView.setVisibility(View.VISIBLE);
                    getCategoryList(false, "", true);
                    Utils.hideKeyboard(getActContext());
                } else {


                    if (s.length() > 2) {
                        isSearch = true;
                        loaderView.setVisibility(View.VISIBLE);
                        imageCancel.setVisibility(View.GONE);
                        category_list.setVisibility(View.GONE);
                        new Handler().postDelayed(() -> getCategoryList(false, searchTxtView.getText().toString().trim(), true), 750);
                    }

                }
            }
        });

        category_list.setFastScrollEnabled(false);
        category_list.setFastScrollAlwaysVisible(false);

        if (app_type.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX)) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_OTHER_SERVICES"));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_SERVICES"));
        }


        categoryitems_list = new ArrayList<>();
        getCategoryList(false, "", false);


        category_list.setOnScrollListener(new AbsListView.OnScrollListener() {
            @Override
            public void onScrollStateChanged(AbsListView view, int scrollState) {

            }

            @Override
            public void onScroll(AbsListView view, int firstVisibleItem, int visibleItemCount, int totalItemCount) {

                int lastInScreen = firstVisibleItem + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                    mIsLoading = true;
                    addFooterView();
                    getCategoryList(true, searchTxtView.getText().toString().trim(), false);
                } else if (!isNextPageAvailable) {
                    removeFooterView();
                }

            }
        });
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    private void addFooterView() {
        removeFooterView();
        if (footerListView == null) {
            footerListView = (LayoutInflater.from(getActContext())).inflate(R.layout.footer_list, category_list, false);
        }
        category_list.addFooterView(footerListView);
    }

    private void removeFooterView() {
        if (footerListView == null) {
            return;
        }
        category_list.removeFooterView(footerListView);
        footerListView = null;
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.imageCancel) {
            loaderView.setVisibility(View.GONE);
            searchTxtView.setText("");
            category_list.setVisibility(View.GONE);
            getCategoryList(false, "", true);
        }
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        removeFooterView();
    }

    private Context getActContext() {
        return UfxCategoryActivity.this;
    }

    private void closeLoader() {
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
        }
    }

    private void getCategoryList(final boolean isLoadMore, String searchText, boolean isSearch) {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (!isSearch) {
            if (loading.getVisibility() != View.VISIBLE) {
                loading.setVisibility(!isLoadMore ? View.VISIBLE : View.GONE);
            }
        }

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getvehicleCategory");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("iVehicleCategoryId", UBERX_PARENT_CAT_ID);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }
        if (searchText.length() > 2) {
            loading.setVisibility(View.GONE);
            parameters.put("search_keyword", searchText);
        }
        if (currentCallExeWebServer != null) {
            currentCallExeWebServer.cancel(true);
            currentCallExeWebServer = null;
        }


        currentCallExeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {
            if (generalFunc.getJsonValueStr("ENABLE_SEARCH_UFX_SERVICES", obj_userProfile).equalsIgnoreCase("YES")) {
                searchView.setVisibility(View.VISIBLE);
            } else {
                searchView.setVisibility(View.GONE);
            }
            pinnedSectionListAdapter = null;
            categoryitems_list.clear();

            noResTxt.setVisibility(View.GONE);
            loaderView.setVisibility(View.GONE);

            if (this.isSearch) {
                imageCancel.setVisibility(View.VISIBLE);
            }

            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    category_list.setVisibility(View.VISIBLE);


                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObject);

                    int sectionPosition = 0, listPosition = 0;

                    if (!UBERX_PARENT_CAT_ID.equalsIgnoreCase("0")) {
                        CategoryListItem[] sections = new CategoryListItem[generalFunc.getJsonArray(Utils.message_str, responseStringObject).length()];

                        JSONArray mainListArr = generalFunc.getJsonArray(Utils.message_str, responseStringObject);
                        if (pinnedSectionListAdapter == null) {
                            pinnedSectionListAdapter = new PinnedCategorySectionListAdapter(getActContext(), generalFunc, categoryitems_list, sections);
                            category_list.setAdapter(pinnedSectionListAdapter);
                        }

                        pinnedSectionListAdapter.setCountryClickListener(UfxCategoryActivity.this);


                        for (int j = 0; j < mainListArr.length(); j++) {

                            CategoryListItem section = new CategoryListItem(CategoryListItem.getSECTION(), "0");
                            section.setSectionPosition(sectionPosition);
                            section.setListPosition(listPosition++);
                            section.setCountSubItems(GeneralFunctions.parseIntegerValue(0, j + ""));
                            sections[sectionPosition] = section;

                            JSONObject subTempJson = generalFunc.getJsonObject(mainListArr, j);

                            CategoryListItem categoryListItem = new CategoryListItem(CategoryListItem.getITEM(), generalFunc.getJsonValueStr("vTitle", subTempJson));
                            categoryListItem.setSectionPosition(sectionPosition);
                            categoryListItem.setListPosition(listPosition++);
                            categoryListItem.setvTitle(generalFunc.getJsonValueStr("vTitle", subTempJson));
                            String resizeImageUrl = Utils.getResizeImgURL(UfxCategoryActivity.this, generalFunc.getJsonValueStr("vLogo_image", subTempJson), 50, 50);
                            categoryListItem.setvLogo(resizeImageUrl);
                            categoryListItem.setvBGColor(generalFunc.getJsonValueStr("vLogo_BG_color", subTempJson));
                            categoryListItem.setvLogo_TINT_color(generalFunc.getJsonValueStr("vLogo_TINT_color", subTempJson));
                            categoryListItem.setiVehicleCategoryId(generalFunc.getJsonValueStr("iVehicleCategoryId", subTempJson));

                            categoryListItem.setVideoConsultEnable(generalFunc.getJsonValueStr("eVideoConsultEnable", subTempJson));
                            categoryListItem.setVideoConsultEnableProvider(generalFunc.getJsonValueStr("eVideoConsultEnableProvider", subTempJson));

                            categoryitems_list.add(categoryListItem);
                            sectionPosition++;
                        }

                    } else {

                        JSONArray mainListArr = generalFunc.getJsonArray(Utils.message_str, responseStringObject);
                        CategoryListItem[] sections = null;


                        if (pinnedSectionListAdapter != null) {
                            sectionPosition = pinnedSectionListAdapter.getSections().length - 1;
                            listPosition = pinnedSectionListAdapter.getSections().length - 1;

                            sections = new CategoryListItem[pinnedSectionListAdapter.getSections().length + mainListArr.length()];

                            for (int i = 0; i < pinnedSectionListAdapter.getSections().length; i++) {
                                sections[i] = pinnedSectionListAdapter.getSections()[i];
                            }

                        } else {
                            sections = new CategoryListItem[mainListArr.length()];
                        }


                        for (int i = 0; i < mainListArr.length(); i++) {
                            JSONObject tempJson = generalFunc.getJsonObject(mainListArr, i);
                            String vCategory = generalFunc.getJsonValueStr("vCategory", tempJson);
                            CategoryListItem section = new CategoryListItem(CategoryListItem.getSECTION(), vCategory);
                            section.setSectionPosition(sectionPosition);
                            section.setListPosition(listPosition++);
                            section.setCountSubItems(GeneralFunctions.parseIntegerValue(0, vCategory));

                            sections[sectionPosition] = section;

                            categoryitems_list.add(section);

                            JSONArray subListArr = generalFunc.getJsonArray("SubCategory", tempJson);

                            for (int j = 0; j < subListArr.length(); j++) {
                                JSONObject subTempJson = generalFunc.getJsonObject(subListArr, j);

                                CategoryListItem categoryListItem = new CategoryListItem(CategoryListItem.getITEM(), generalFunc.getJsonValueStr("vCategory", tempJson));
                                categoryListItem.setSectionPosition(sectionPosition);
                                categoryListItem.setListPosition(listPosition++);
                                categoryListItem.setvTitle(generalFunc.getJsonValueStr("vTitle", subTempJson));
                                String resizeImageUrl = Utils.getResizeImgURL(UfxCategoryActivity.this, generalFunc.getJsonValueStr("vLogo_image", subTempJson), 50, 50);
                                categoryListItem.setvLogo(resizeImageUrl);
                                categoryListItem.setvBGColor(generalFunc.getJsonValueStr("vLogo_BG_color", subTempJson));
                                categoryListItem.setvLogo_TINT_color(generalFunc.getJsonValueStr("vLogo_TINT_color", subTempJson));
                                categoryListItem.setiVehicleCategoryId(generalFunc.getJsonValueStr("iVehicleCategoryId", subTempJson));

                                categoryListItem.setVideoConsultEnable(generalFunc.getJsonValueStr("eVideoConsultEnable", subTempJson));
                                categoryListItem.setVideoConsultEnableProvider(generalFunc.getJsonValueStr("eVideoConsultEnableProvider", subTempJson));

                                categoryitems_list.add(categoryListItem);
                            }

                            sectionPosition++;
                        }

                        if (pinnedSectionListAdapter == null) {
                            pinnedSectionListAdapter = new PinnedCategorySectionListAdapter(getActContext(), generalFunc, categoryitems_list, sections);
                            category_list.setAdapter(pinnedSectionListAdapter);
                            pinnedSectionListAdapter.setCountryClickListener(UfxCategoryActivity.this);

                        } else {
                            pinnedSectionListAdapter.changeSection(sections);
                        }
                    }
                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                    pinnedSectionListAdapter.notifyDataSetChanged();
                } else {
                    noResTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    noResTxt.setVisibility(View.VISIBLE);
                }
            } else {
                generateErrorView();
            }
            closeLoader();

            mIsLoading = false;
        });

    }

    private void generateErrorView() {
        closeLoader();
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getCategoryList(false, searchTxtView.getText().toString().trim(), false));
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.UFX_REQUEST_CODE && resultCode == RESULT_OK && data != null) {
            mCountryListItem.setVideoConsultEnableProvider(data.getStringExtra("eVideoConsultEnableProvider"));
            pinnedSectionListAdapter.notifyDataSetChanged();
        }
    }

    @Override
    public void countryClickList(CategoryListItem countryListItem) {
        Bundle bn = new Bundle();
        bn.putString("iVehicleCategoryId", countryListItem.getiVehicleCategoryId());
        bn.putString("vTitle", countryListItem.getvTitle());
        mCountryListItem = countryListItem;
        (new ActUtils(getActContext())).startActForResult(AddServiceActivity.class, bn, Utils.UFX_REQUEST_CODE);
    }
}