package com.act;

import android.Manifest;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.AbsListView;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.core.app.ActivityCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.CategoryListItem;
import com.adapter.files.PinnedBiddingServicesListAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityBiddingCategoryBinding;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;

public class BiddingCategoryActivity extends ParentActivity {

    private ActivityBiddingCategoryBinding binding;
    private static final int ADD_ADDRESS = 67;

    private String next_page_str = "", eSelectWorkLocation = "Fixed";

    private final ArrayList<CategoryListItem> mBiddingList = new ArrayList<>();
    private CategoryListItem[] mSections;
    private PinnedBiddingServicesListAdapter pinnedBiddingServicesListAdapter;

    private boolean mIsLoading = false, isNextPageAvailable = false, isSearch = false;

    private View footerListView;
    private ServerTask currentCallExeWebServer;
    private MButton btnBiddingService;

    private GenerateAlertBox currentAlertBox;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_bidding_category);

        initViews();
        locationView();
        searchView();

        binding.categoryList.setShadowVisible(true);
        binding.categoryList.setFastScrollEnabled(false);
        binding.categoryList.setFastScrollAlwaysVisible(false);
        binding.categoryList.setOnScrollListener(new AbsListView.OnScrollListener() {
            @Override
            public void onScrollStateChanged(AbsListView view, int scrollState) {

            }

            @Override
            public void onScroll(AbsListView view, int firstVisibleItem, int visibleItemCount, int totalItemCount) {

                int lastInScreen = firstVisibleItem + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                    mIsLoading = true;
                    addFooterView();
                    getCategoryList(true, Utils.getText(binding.searchTxtView), false);
                } else if (!isNextPageAvailable) {
                    removeFooterView();
                }

            }
        });

        pinnedBiddingServicesListAdapter = new PinnedBiddingServicesListAdapter(getActContext(), generalFunc, mBiddingList, mSections);
        binding.categoryList.setAdapter(pinnedBiddingServicesListAdapter);

        getCategoryList(false, "", false);
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);

        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_BIDDING_SERVICES"));

        binding.introTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_SERVICE_INTRO_TXT"));

        //
        addToClickHandler(binding.imageCancel);
        binding.imageCancel.setVisibility(View.GONE);
        binding.searchView.setVisibility(View.GONE);
        binding.loaderView.setVisibility(View.GONE);

        btnBiddingService = ((MaterialRippleLayout) binding.btnBiddingService).getChildView();
        btnBiddingService.setText(generalFunc.retrieveLangLBl("", "LBL_UPDATE_SERVICES"));
        btnBiddingService.setVisibility(View.GONE);
        btnBiddingService.setId(Utils.generateViewId());
        addToClickHandler(btnBiddingService);
    }

    private void locationView() {
        binding.workLocTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_JOB_LOCATION_TXT"));
        binding.editLocation.setOnClickListener(v -> {

            ArrayList<String> requestPermissions = new ArrayList<>();
            requestPermissions.add(Manifest.permission.ACCESS_FINE_LOCATION);
            requestPermissions.add(Manifest.permission.ACCESS_COARSE_LOCATION);
            if (generalFunc.isAllPermissionGranted(false, requestPermissions, LOCATION_PERMISSIONS_REQUEST)) {
                addAddressActivity();
            } else {
                generalFunc.isAllPermissionGranted(true, requestPermissions, LOCATION_PERMISSIONS_REQUEST);
            }
        });
        handleWorkAddress(generalFunc.retrieveValue(Utils.WORKLOCATION));
    }

    private void searchView() {
        binding.searchTxtView.setHint(generalFunc.retrieveLangLBl("", "LBL_SEARCH_SERVICES"));
        binding.searchTxtView.addTextChangedListener(new TextWatcher() {
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
                    binding.imageCancel.setVisibility(View.GONE);
                    binding.loaderView.setVisibility(View.VISIBLE);
                    getCategoryList(false, "", true);
                    Utils.hideKeyboard(getActContext());
                } else {
                    if (s.length() > 2) {
                        isSearch = true;
                        binding.loaderView.setVisibility(View.VISIBLE);
                        binding.imageCancel.setVisibility(View.GONE);
                        binding.categoryList.setVisibility(View.GONE);
                        new Handler(Looper.getMainLooper()).postDelayed(() -> {
                            //
                            getCategoryList(false, Utils.getText(binding.searchTxtView), true);
                        }, 750);
                    }
                }
            }
        });
    }

    private void addAddressActivity() {
        Bundle bn = new Bundle();
        bn.putString("latitude", generalFunc.getJsonValueStr("vWorkLocationLatitude", obj_userProfile));
        bn.putString("longitude", generalFunc.getJsonValueStr("vWorkLocationLongitude", obj_userProfile));
        bn.putString("address", Utils.getText(binding.addressTxt));
        new ActUtils(getActContext()).startActForResult(AddAddressActivity.class, bn, ADD_ADDRESS);
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);

        if (requestCode == LOCATION_PERMISSIONS_REQUEST) {
            ArrayList<String> requestPermissions = new ArrayList<>();
            Collections.addAll(requestPermissions, permissions);
            if (generalFunc.isAllPermissionGranted(false, requestPermissions, requestCode)) {
                addAddressActivity();
            } else {
                int myCount = 0;
                for (String permission : permissions) {
                    if (!ActivityCompat.shouldShowRequestPermissionRationale(BiddingCategoryActivity.this, permission)) {
                        myCount++;
                    }
                }
                if (permissions.length == myCount) {
                    showNoLocationPermission(requestCode);
                }
            }
        }
    }

    private void showNoLocationPermission(int requestCode) {
        currentAlertBox = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Application requires some permission to be granted to work. Please allow it.",
                        "LBL_LOC_ALLOW_NOTE_ANDROID"), generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("Allow All", "LBL_SETTINGS"),
                buttonId -> {
                    if (buttonId == 0) {
                        currentAlertBox.closeAlertBox();
                    } else {
                        generalFunc.openSettings(true, requestCode);
                    }
                });
    }

    private void handleWorkAddress(String WORKLOCATION) {
        if (Utils.checkText(WORKLOCATION)) {
            binding.addressTxt.setText(WORKLOCATION);
            binding.editLocation.setImageResource(R.drawable.ic_location_edit);
        } else {
            binding.addressTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_ADDRESS_TXT"));
            binding.editLocation.setImageResource(R.drawable.ic_add_);
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == ADD_ADDRESS) {
            if (resultCode == RESULT_OK) {
                String worklat = data.getStringExtra("Latitude");
                String worklong = data.getStringExtra("Longitude");
                String workadddress = data.getStringExtra("Address");
                updateWorkLocation(worklat, worklong, workadddress);
            }
        }
        if (requestCode == LOCATION_PERMISSIONS_REQUEST) {
            if (generalFunc.isLocationPermissionGranted(false)) {
                addAddressActivity();
            }
        }
    }

    private void updateWorkLocation(String worklat, String worklong, String workaddress) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverWorkLocationUFX");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("vWorkLocationLatitude", worklat);
        parameters.put("vWorkLocationLongitude", worklong);
        parameters.put("vWorkLocation", workaddress);

        if (generalFunc.retrieveValue(Utils.WORKLOCATION).equals("")) {
            parameters.put("eSelectWorkLocation", eSelectWorkLocation);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (generalFunc.retrieveValue(Utils.WORKLOCATION).equals("")) {
                        eSelectWorkLocation = "Fixed";
                        parameters.put("eSelectWorkLocation", eSelectWorkLocation);
                    }
                    binding.addressTxt.setText(workaddress);
                    generalFunc.storeData(Utils.WORKLOCATION, workaddress);
                    handleWorkAddress(workaddress);
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void addFooterView() {
        removeFooterView();
        if (footerListView == null) {
            footerListView = (LayoutInflater.from(getActContext())).inflate(R.layout.footer_list, binding.categoryList, false);
        }
        binding.categoryList.addFooterView(footerListView);
    }

    private void removeFooterView() {
        if (footerListView == null) {
            return;
        }
        binding.categoryList.removeFooterView(footerListView);
        footerListView = null;
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        removeFooterView();
    }

    private Context getActContext() {
        return BiddingCategoryActivity.this;
    }

    private void getCategoryList(final boolean isLoadMore, String searchText, boolean isSearch) {
        binding.errorView.setVisibility(View.GONE);
        if (!isSearch) {
            if (binding.loading.getVisibility() != View.VISIBLE) {
                binding.loading.setVisibility(!isLoadMore ? View.VISIBLE : View.GONE);
            }
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getBiddingServices");
        parameters.put("iDriverId", generalFunc.getMemberId());
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }
        if (searchText.length() > 2) {
            binding.loading.setVisibility(View.GONE);
            parameters.put("search_keyword", searchText);
        }
        if (currentCallExeWebServer != null) {
            currentCallExeWebServer.cancel(true);
            currentCallExeWebServer = null;
        }

        currentCallExeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {

            currentCallExeWebServer = null;

            if (generalFunc.getJsonValueStr("ENABLE_SEARCH_UFX_SERVICES", obj_userProfile).equalsIgnoreCase("YES")) {
                binding.searchView.setVisibility(View.VISIBLE);
            } else {
                binding.searchView.setVisibility(View.GONE);
            }
            mBiddingList.clear();

            binding.noResTxt.setVisibility(View.GONE);
            binding.loaderView.setVisibility(View.GONE);

            if (this.isSearch) {
                binding.imageCancel.setVisibility(View.VISIBLE);
            }

            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null && !responseStringObject.toString().equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    binding.categoryList.setVisibility(View.VISIBLE);
                    btnBiddingService.setVisibility(View.VISIBLE);

                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObject);

                    mSections = new CategoryListItem[generalFunc.getJsonArray(Utils.message_str, responseStringObject).length()];
                    JSONArray mainListArr = generalFunc.getJsonArray(Utils.message_str, responseStringObject);

                    int sectionPosition = 0, listPosition = 0;
                    for (int i = 0; i < mainListArr.length(); i++) {
                        JSONObject tempJson = generalFunc.getJsonObject(mainListArr, i);
                        String vCategory = generalFunc.getJsonValueStr("vCategory", tempJson);
                        CategoryListItem section = new CategoryListItem(CategoryListItem.getSECTION(), vCategory);
                        section.setSectionPosition(sectionPosition);
                        section.setListPosition(listPosition++);
                        section.setCountSubItems(GeneralFunctions.parseIntegerValue(0, vCategory));

                        mSections[sectionPosition] = section;

                        mBiddingList.add(section);

                        JSONArray subListArr = generalFunc.getJsonArray("SubCategory", tempJson);

                        for (int j = 0; j < subListArr.length(); j++) {
                            JSONObject subTempJson = generalFunc.getJsonObject(subListArr, j);

                            CategoryListItem categoryListItem = new CategoryListItem(CategoryListItem.getITEM(), generalFunc.getJsonValueStr("vCategory", tempJson));
                            categoryListItem.setSectionPosition(sectionPosition);
                            categoryListItem.setListPosition(listPosition++);
                            categoryListItem.setvTitle(generalFunc.getJsonValueStr("vTitle", subTempJson));
                            String resizeImageUrl = Utils.getResizeImgURL(getActContext(), generalFunc.getJsonValueStr("vLogo_image", subTempJson), 50, 50);
                            categoryListItem.setvLogo(resizeImageUrl);

                            categoryListItem.setiVehicleCategoryId(generalFunc.getJsonValueStr("iBiddingId", subTempJson));
                            categoryListItem.setvCategory(generalFunc.getJsonValueStr("eServiceRequest", subTempJson));

                            mBiddingList.add(categoryListItem);
                        }
                        sectionPosition++;
                    }

                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                    pinnedBiddingServicesListAdapter.changeSection(mSections);
                    pinnedBiddingServicesListAdapter.notifyDataSetChanged();
                    pinnedBiddingServicesListAdapter.manageBiddingArraySize();
                } else {
                    binding.noResTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    binding.noResTxt.setVisibility(View.VISIBLE);
                    btnBiddingService.setVisibility(View.GONE);
                }
            } else {
                generateErrorView();
            }

            binding.loading.setVisibility(View.GONE);
            mIsLoading = false;
        });
    }

    private void generateErrorView() {
        binding.loading.setVisibility(View.GONE);
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setVisibility(View.VISIBLE);
        binding.errorView.setOnRetryListener(() -> getCategoryList(false, Utils.getText(binding.searchTxtView), false));
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();

        } else if (i == binding.imageCancel.getId()) {
            binding.loaderView.setVisibility(View.GONE);
            binding.searchTxtView.setText("");
            binding.categoryList.setVisibility(View.GONE);
            getCategoryList(false, "", true);

        } else if (i == btnBiddingService.getId()) {
            if (Utils.checkText(generalFunc.retrieveValue(Utils.WORKLOCATION))) {
                String selectedIDList = pinnedBiddingServicesListAdapter.getSelectedIDList();
                addService(selectedIDList);
            } else {
                generalFunc.showMessage(binding.addressTxt, generalFunc.retrieveLangLBl("", "LBL_ENTER_WORK_LOC_TXT"));
            }
        }
    }

    private void addService(String selectedIDList) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateDriverBiddingServices");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("iBiddingId", selectedIDList);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null && !responseStringObject.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)), true);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }
}