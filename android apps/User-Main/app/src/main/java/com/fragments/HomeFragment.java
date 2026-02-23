package com.fragments;

import android.animation.LayoutTransition;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.Drawable;
import android.location.Location;
import android.os.Build;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatDialog;
import androidx.appcompat.content.res.AppCompatResources;
import androidx.coordinatorlayout.widget.CoordinatorLayout;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.drawable.DrawableCompat;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.viewpager.widget.ViewPager;

import com.ViewPagerCards.CardPagerAdapter;
import com.ViewPagerCards.ShadowTransformer;
import com.act.MainActivity;
import com.act.SearchLocationActivity;
import com.act.UberXHomeActivity;
import com.act.UberXSelectServiceActivity;
import com.adapter.files.UberXCategoryAdapter;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.DividerItemDecoration;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.OpenCatType;
import com.general.files.OpenNoLocationView;
import com.google.android.material.appbar.AppBarLayout;
import com.google.android.material.appbar.CollapsingToolbarLayout;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.skyfishjy.library.RippleBackground;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;

public class HomeFragment extends BaseFragment implements UberXCategoryAdapter.OnItemClickList, ViewPager.OnPageChangeListener
        , GetAddressFromLocation.AddressFound, GetLocationUpdates.LocationUpdates {

    View view;

    public String userProfileJson = "";
    public String UBERX_PARENT_CAT_ID = "";
    public JSONObject obj_userProfile;
    RecyclerView dataListRecyclerView;
    public View bannerArea;

    ProgressBar serviceLoadingProgressBar;

    ViewPager bannerViewPager;
    GeneralFunctions generalFunc;
    //AddDrawer addDrawer;
    public MTextView headerLocAddressTxt, LocStaticTxt, selectServiceTxt;
    HashMap<String, String> generalCategoryIconTypeDataMap = new HashMap<>();
    ArrayList<HashMap<String, String>> generalCategoryList = new ArrayList<>();

    ArrayList<HashMap<String, String>> mainCategoryList = new ArrayList<>();
    ArrayList<HashMap<String, String>> subCategoryList = new ArrayList<>();

    ArrayList<HashMap<String, String>> allMainCategoryList = new ArrayList<>();

    UberXCategoryAdapter ufxCatAdapter;
    public String CAT_TYPE_MODE = "0";
    public String latitude = "0.0";
    public String longitude = "0.0";

    public ImageView backImgView;
    String mAddress;
    public GetLocationUpdates getLastLocation;
    public boolean isUfxaddress = false;
    GetAddressFromLocation getAddressFromLocation;
    boolean isback = false;
    public LinearLayout btnArea, MainLayout;
    LinearLayout uberXHeaderLayout;
    public RelativeLayout MainTopArea, MainArea;
    InternetConnection intCheck;

    DividerItemDecoration itemDecoration;
    MButton btn_type2;
    int submitBtnId;
    public String vParentCategoryName = "";
    String parentId = "";
    boolean isbanner = false;

    public ArrayList<String> multiServiceSelect = new ArrayList<>();

    private CardPagerAdapter mCardAdapter;
    private ShadowTransformer mCardShadowTransformer;
    AppBarLayout appBarLayout, appBarLayoutMain;
    public UberXHomeActivity uberXHomeActivity;
    String MORE_ICON = "";
    int noColumns;
    int color = Color.parseColor("#FFFFFF");
    int color1 = Color.parseColor("#f1f1f1");
    public boolean isLoading = false;


    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

        view = inflater.inflate(R.layout.activity_uber_x, container, false);
        MainArea = view.findViewById(R.id.MainArea);
        MainTopArea = view.findViewById(R.id.MainTopArea);


        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());
        uberXHomeActivity = (UberXHomeActivity) getActivity();


        isUfxaddress = false;

        setUserProfileData("", false);
        btn_type2 = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2)).getChildView();
        appBarLayout = (AppBarLayout) view.findViewById(R.id.app_bar_layout);
        appBarLayoutMain = (AppBarLayout) view.findViewById(R.id.appBarLayout);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            appBarLayoutMain.setOutlineProvider(null);
        }
        CollapsingToolbarLayout ctl = (CollapsingToolbarLayout) view.findViewById(R.id.collapsing_toolbar);
        ctl.setTitle("Best Coupons Deals");


        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);
        addToClickHandler(btn_type2);

        itemDecoration = new DividerItemDecoration(getActContext(), DividerItemDecoration.VERTICAL_LIST);
        intCheck = new InternetConnection(getActContext());

        if (getArguments() != null) {
            mAddress = getArguments().getString("uberXAddress");
            this.latitude = getArguments().getString("uberXlat");
            this.longitude = getArguments().getString("uberXlong");
            isback = getArguments().getBoolean("isback", false);
            if (!this.latitude.equalsIgnoreCase("0.0") && !this.longitude.equalsIgnoreCase("0.0")) {
                isUfxaddress = true;
                headerLocAddressTxt.setText(mAddress);
            }
        }

        if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
            if (!isUfxaddress) {
                generalFunc.isLocationPermissionGranted(true);
            }
        }

        btnArea = view.findViewById(R.id.btnArea);
        MainLayout = view.findViewById(R.id.MainLayout);


        serviceLoadingProgressBar = view.findViewById(R.id.serviceLoadingProgressBar);


        uberXHeaderLayout = view.findViewById(R.id.uberXHeaderLayout);
        selectServiceTxt = (MTextView) view.findViewById(R.id.selectServiceTxt);


        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));


        dataListRecyclerView = (RecyclerView) view.findViewById(R.id.dataListRecyclerView);
        bannerArea = view.findViewById(R.id.bannerArea);
        bannerViewPager = (ViewPager) view.findViewById(R.id.bannerViewPager);

        boolean isRideDeliveryUberx = ServiceModule.RideDeliveryUbexProduct;
        if (isRideDeliveryUberx || ServiceModule.isServiceProviderOnly()) {
            bannerArea.setVisibility(View.GONE);
            if (isRideDeliveryUberx) {
                uberXHeaderLayout.setVisibility(View.VISIBLE);
            }

            if (UBERX_PARENT_CAT_ID.equalsIgnoreCase("0")) {
                btnArea.setVisibility(View.GONE);
            } else {
                btnArea.setVisibility(View.VISIBLE);
            }

        } else {
            bannerArea.setVisibility(View.VISIBLE);

            btnArea.setVisibility(View.VISIBLE);

            uberXHeaderLayout.setVisibility(View.VISIBLE);
            CollapsingToolbarLayout.LayoutParams lyParamsBannerArea = (CollapsingToolbarLayout.LayoutParams) bannerArea.getLayoutParams();
            lyParamsBannerArea.height = Utils.getHeightOfBanner(getActContext(), 0, "16:9");
            bannerArea.setLayoutParams(lyParamsBannerArea);
        }

        headerLocAddressTxt = (MTextView) view.findViewById(R.id.headerLocAddressTxt);
        Drawable drawable = AppCompatResources.getDrawable(getActContext(), R.drawable.ic_place_address);

        Drawable wrappedDrawable = DrawableCompat.wrap(drawable);
        Drawable mutableDrawable = wrappedDrawable.mutate();
        DrawableCompat.setTint(mutableDrawable, ContextCompat.getColor(getActContext(), R.color.white));

        Drawable arrowdrawable;
        arrowdrawable = AppCompatResources.getDrawable(getActContext(), R.drawable.ic_down_arrow_header);


        Drawable arrowwrappedDrawable = DrawableCompat.wrap(arrowdrawable);
        Drawable arrowmutableDrawable = arrowwrappedDrawable.mutate();
        DrawableCompat.setTint(arrowmutableDrawable, ContextCompat.getColor(getActContext(), R.color.white));

        if (generalFunc.isRTLmode()) {
            headerLocAddressTxt.setCompoundDrawablesWithIntrinsicBounds(arrowmutableDrawable, null, mutableDrawable, null);
        } else {
            headerLocAddressTxt.setCompoundDrawablesWithIntrinsicBounds(mutableDrawable, null, arrowmutableDrawable, null);
        }


        LocStaticTxt = (MTextView) view.findViewById(R.id.LocStaticTxt);
        LocStaticTxt.setVisibility(View.GONE);

        backImgView = (ImageView) view.findViewById(R.id.backImgView);


        backImgView.setOnClickListener(new setOnClickLst());


        if (isback) {

            backImgView.setVisibility(View.VISIBLE);
            manageToolBarAddressView(true);
        }


        ufxCatAdapter = new UberXCategoryAdapter(getActContext(), generalCategoryList, generalFunc);

        if (UBERX_PARENT_CAT_ID.equalsIgnoreCase("0")) {
            CAT_TYPE_MODE = "0";
            noColumns = getNumOfColumns();
            setParentCategoryLayoutManager();
            btnArea.setVisibility(View.GONE);
        } else {
            dataListRecyclerView.setLayoutManager(new LinearLayoutManager(getActContext()));

            CAT_TYPE_MODE = "1";
        }
        dataListRecyclerView.setAdapter(ufxCatAdapter);

        uberXHeaderLayout.setOnClickListener(new setOnClickLst());


        setData();

        mainCategoryList.clear();
        subCategoryList.clear();
        Logger.e("geCtaegory", "::oncreatecalled");

        bannerViewPager.addOnPageChangeListener(this);

        if (!uberXHomeActivity.ENABLE_LOCATION_WISE_BANNER.equalsIgnoreCase("Yes") || isUfxaddress) {

            getCategory(UBERX_PARENT_CAT_ID, CAT_TYPE_MODE);

        }
        manageMainCatShimmer();

        return view;
    }

    public void manageMainCatShimmer() {
        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(MainLayout, R.layout.home_shimmer_view);
    }

    public void manageMainSubCatShimmer() {
        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(MainTopArea, R.layout.subcategory_shimmer_view);
    }

    private void setUserProfileData(String userProfileData, boolean isSet) {
        userProfileJson = isSet ? userProfileData : generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        UBERX_PARENT_CAT_ID = generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile);
    }

    private void initializeLocation() {
        if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
            manageMainCatShimmer();
        } else {
            manageMainSubCatShimmer();
        }
        headerLocAddressTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
        stopLocationUpdates();
        GetLocationUpdates.locationResolutionAsked = false;
        getLastLocation = new GetLocationUpdates(getActContext(), Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);
    }

    public void initializeLocationCheckDone() {
        if (generalFunc.isLocationPermissionGranted(false) && generalFunc.isLocationEnabled()) {
            if (isUfxaddress) {
                stopLocationUpdates();
                Location temploc = new Location("PickupLoc");
                temploc.setLatitude(Double.parseDouble(latitude));
                temploc.setLongitude(Double.parseDouble(longitude));
                onLocationUpdate(temploc);
            } else {
                initializeLocation();
            }
        } else if (generalFunc.isLocationPermissionGranted(false) && !generalFunc.isLocationEnabled()) {
            if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                    generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
                ViewGroup viewGroup = view.findViewById(R.id.MainArea);
                OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
            }
        } else if (isUfxaddress) {
            ViewGroup viewGroup = view.findViewById(R.id.MainArea);
            OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull @NotNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        ViewGroup viewGroup = view.findViewById(R.id.MainArea);
        OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
    }

    public void stopLocationUpdates() {
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
        }
    }

    private void setParentCategoryLayoutManager() {
        GridLayoutManager gridLay = new GridLayoutManager(getActContext(), noColumns);
        gridLay.setSpanSizeLookup(new GridLayoutManager.SpanSizeLookup() {
            @Override
            public int getSpanSize(int position) {
                if (generalCategoryIconTypeDataMap.get("" + position) != null && !generalCategoryIconTypeDataMap.get("" + position).equalsIgnoreCase("ICON")) {
                    if (noColumns > gridLay.getSpanCount()) {
                        return gridLay.getSpanCount();
                    } else {
                        return noColumns;
                    }
                }
                return 1;
            }
        });
        dataListRecyclerView.setLayoutManager(gridLay);
    }

    private void setData() {
        headerLocAddressTxt.setHint(generalFunc.retrieveLangLBl("Enter Location...", "LBL_ENTER_LOC_HINT_TXT"));
        LocStaticTxt.setText(generalFunc.retrieveLangLBl("Location For availing Service", "LBL_LOCATION_FOR_AVAILING_TXT"));
        selectServiceTxt.setText(generalFunc.retrieveLangLBl("Select Service", "LBL_RDU_SERVICES_TITLE"));
        if (isback) {
            String address = getArguments().getString("address");
            if (address != null && !address.equalsIgnoreCase("")) {
                headerLocAddressTxt.setText(address);
                latitude = getArguments().getString("lat");
                longitude = getArguments().getString("long");

            }
        }
    }

    public Context getActContext() {
        return MyApp.getInstance().getCurrentAct();
    }

    public void getCategory(String parentId, final String CAT_TYPE_MODE) {

        if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
            manageMainCatShimmer();
        } else {
            manageMainSubCatShimmer();
        }

        this.parentId = parentId;
        generalCategoryList.clear();

        if (!CAT_TYPE_MODE.equals("0")) {
            subCategoryList.clear();
            manageToolBarAddressView(true);
            MainLayout.setBackgroundColor(color1);
            MainArea.setBackgroundColor(color1);
            bannerArea.setBackgroundColor(color1);
            selectServiceTxt.setBackgroundColor(color1);

            int categoryId = generalFunc.parseIntegerValue(0, UBERX_PARENT_CAT_ID);
            Logger.d("CatID", "categoryId" + categoryId);
            if (categoryId > 0) {
                backImgView.setVisibility(View.GONE);
                uberXHomeActivity.binding.rduTopArea.setVisibility(View.VISIBLE);
            } else {
                backImgView.setVisibility(View.VISIBLE);
                uberXHomeActivity.binding.rduTopArea.setVisibility(View.GONE);
            }

        } else {
            backImgView.setVisibility(View.GONE);
            manageToolBarAddressView(false);
            uberXHomeActivity.binding.rduTopArea.setVisibility(View.VISIBLE);
            MainLayout.setBackgroundColor(color);
            MainArea.setBackgroundColor(color);
            bannerArea.setBackgroundColor(color);
            selectServiceTxt.setBackgroundColor(color);
        }


        serviceLoadingProgressBar.setVisibility(View.GONE);
        btnArea.setVisibility(View.GONE);

        if (ufxCatAdapter != null) {
            dataListRecyclerView.getRecycledViewPool().clear();
            ufxCatAdapter.notifyDataSetChanged();
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategories");
        parameters.put("parentId", "" + parentId);
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("vLatitude", uberXHomeActivity.mSelectedLatitude);
        parameters.put("vLongitude", uberXHomeActivity.mSelectedLongitude);

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            subCategoryList.clear();
            generalCategoryList.clear();
            btnArea.setVisibility(View.VISIBLE);
            if (responseObj != null && !responseObj.toString().equals("")) {

                MORE_ICON = generalFunc.getJsonValueStr("MORE_ICON", responseObj);

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseObj);
                if (this.CAT_TYPE_MODE.equalsIgnoreCase("0")
                        && !CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                    serviceLoadingProgressBar.setVisibility(View.GONE);

                    return;

                }


                if (isDataAvail) {
                    isLoading = false;
                    generalCategoryList.clear();

                    int GRID_TILES_MAX_COUNT = GeneralFunctions.parseIntegerValue(1, generalFunc.getJsonValueStr("GRID_TILES_MAX_COUNT", obj_userProfile));

                    JSONArray mainCataArray = generalFunc.getJsonArray("message", responseObj);

                    ArrayList<HashMap<String, String>> bannerList = new ArrayList<>();
                    ArrayList<HashMap<String, String>> serviceList = new ArrayList<>();
                    ArrayList<HashMap<String, String>> moreItemList = new ArrayList<>();

                    int gridCount = 0;
                    if (CAT_TYPE_MODE.equals("0")) {
                        allMainCategoryList.clear();
                        mainCategoryList.clear();
                    }
                    vParentCategoryName = generalFunc.getJsonValueStr("vParentCategoryName", responseObj);

                    int mainCatArraySize = mainCataArray.length();
                    String LBL_BOOK_NOW = generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW");
                    boolean APP_HOME_PAGE_LIST_VIEW_ENABLED = generalFunc.getJsonValue("APP_HOME_PAGE_LIST_VIEW_ENABLED", userProfileJson).equalsIgnoreCase("Yes");
                    boolean isAppType = ServiceModule.RideDeliveryUbexProduct
                            || (ServiceModule.isServiceProviderOnly());

                    for (int i = 0; i < mainCatArraySize; i++) {
                        HashMap<String, String> map = new HashMap<String, String>();
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

                        if (CAT_TYPE_MODE.equals("0")) {
                            btnArea.setVisibility(View.GONE);
                            allMainCategoryList.add(map);
                            if (isAppType) {
                                String eShowType = generalFunc.getJsonValueStr("eShowType", categoryObj);
                                map.put("eShowType", eShowType);

                                //add condition for manage lsiting in home screen
                                if (!APP_HOME_PAGE_LIST_VIEW_ENABLED) {

                                    if (eShowType.equalsIgnoreCase("ICON") || eShowType.equalsIgnoreCase("ICON-BANNER")) {
                                        map.put("eShowType", "Icon");

                                        if (gridCount < GRID_TILES_MAX_COUNT) {
                                            mainCategoryList.add((HashMap<String, String>) map.clone());
                                        } else {
                                            moreItemList.add((HashMap<String, String>) map.clone());
                                        }
                                        gridCount = gridCount + 1;

                                        if (eShowType.equalsIgnoreCase("ICON-BANNER")) {
                                            map.put("eShowType", "Banner");
                                            bannerList.add((HashMap<String, String>) map.clone());
                                        }
                                    } else {
                                        bannerList.add((HashMap<String, String>) map.clone());
                                    }
                                } else {
                                    String eCatViewType = generalFunc.getJsonValueStr("eCatViewType", categoryObj);
                                    if (eCatViewType.equalsIgnoreCase("Icon") || eCatViewType.equalsIgnoreCase("Icon,Banner") ||
                                            eCatViewType.equalsIgnoreCase("Icon,Banner,List") ||
                                            eCatViewType.equalsIgnoreCase("Icon,List")) {
                                        map.put("eShowType", "Icon");

                                        Logger.d("eCatViewType", "::" + eCatViewType);

                                        if (gridCount < GRID_TILES_MAX_COUNT) {
                                            mainCategoryList.add((HashMap<String, String>) map.clone());
                                        } else {
                                            moreItemList.add((HashMap<String, String>) map.clone());
                                        }
                                        gridCount = gridCount + 1;

                                        if (eCatViewType.equalsIgnoreCase("Icon,Banner")) {
                                            map.put("eShowType", "Banner");
                                            bannerList.add((HashMap<String, String>) map.clone());
                                        }
                                        if (eCatViewType.equalsIgnoreCase("Icon,Banner,List")) {
                                            map.put("eShowType", "Banner");
                                            bannerList.add((HashMap<String, String>) map.clone());
                                        }

                                        //added in list for eCatViewType=" icon banner list"
                                        if (eCatViewType.equalsIgnoreCase("Icon,Banner,List")) {
                                            map.put("eShowType", "List");
                                            map.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            map.put("tListDescription", generalFunc.getJsonValueStr("tListDescription", categoryObj));
                                            serviceList.add((HashMap<String, String>) map.clone());

                                        }
                                        if (eCatViewType.equalsIgnoreCase("Icon,List")) {
                                            map.put("eShowType", "List");
                                            map.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            map.put("tListDescription", generalFunc.getJsonValueStr("tListDescription", categoryObj));
                                            serviceList.add((HashMap<String, String>) map.clone());

                                        }

                                    } else if (eCatViewType.equalsIgnoreCase("Banner") ||
                                            eCatViewType.equalsIgnoreCase("Banner,List")) {
                                        map.put("eShowType", "Banner");
                                        bannerList.add((HashMap<String, String>) map.clone());

                                        //added in list for eCatViewType="  banner list"
                                        if (eCatViewType.equalsIgnoreCase("Banner,List")) {
                                            map.put("eShowType", "List");
                                            map.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            map.put("tListDescription", generalFunc.getJsonValueStr("tListDescription", categoryObj));
                                            serviceList.add((HashMap<String, String>) map.clone());
                                        }
                                    } else {
                                        map.put("eShowType", "List");
                                        map.put("tListDescription", generalFunc.getJsonValueStr("tListDescription", categoryObj));
                                        map.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                        serviceList.add((HashMap<String, String>) map.clone());
                                        // added in list only
                                    }

                                }
                            } else {
                                mainCategoryList.add((HashMap<String, String>) map.clone());
                            }
                        } else {
                            subCategoryList.add((HashMap<String, String>) map.clone());
                        }
                    }

                    JSONArray arr = generalFunc.getJsonArray("BANNER_DATA", responseObj);
                    if (arr != null && arr.length() > 0) {
                        isbanner = true;
                        bannerArea.setVisibility(View.VISIBLE);
                        int arrSize = arr.length();
                        ArrayList<String> imagesList = new ArrayList<String>();
                        mCardAdapter = new CardPagerAdapter();
                        int margin = MyApp.getInstance().getCurrentAct().getResources().getDimensionPixelSize(R.dimen._20sdp);
                        int width = margin * 2;
                        int getBannerWidth = Utils.getWidthOfBanner(MyApp.getInstance().getCurrentAct(), width);
                        int size155sdp = MyApp.getInstance().getCurrentAct().getResources().getDimensionPixelSize(R.dimen._155sdp);

                        for (int i = 0; i < arrSize; i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr, i);

                            String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                            String imageURL = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), vImage, getBannerWidth, size155sdp);

                            imagesList.add(imageURL);
                            mCardAdapter.addCardItem(imageURL, getActContext(), getBannerWidth, size155sdp);
                        }

                        int imageListSize = imagesList.size();
                        if (imageListSize > 2) {
                            bannerViewPager.setOffscreenPageLimit(3);
                        } else if (imageListSize > 1) {
                            bannerViewPager.setOffscreenPageLimit(2);
                        }
                        mCardShadowTransformer = new ShadowTransformer(bannerViewPager, mCardAdapter);
                        bannerViewPager.setAdapter(mCardAdapter);
                        bannerViewPager.setPageTransformer(false, mCardShadowTransformer);
                        bannerViewPager.setOffscreenPageLimit(3);

                    } else {
                        isbanner = false;
                        bannerArea.setVisibility(View.GONE);
                    }

                    if (isbanner) {
                        bannerArea.setVisibility(View.VISIBLE);
                    }


                    if (CAT_TYPE_MODE.equals("0")) {
                        if (isAppType) {
                            int moreItemListSize = moreItemList.size();
                            if (moreItemListSize > 0) {

                                HashMap<String, String> mapDataMore = new HashMap<>();
                                mapDataMore.put("eCatType", "More");
                                mapDataMore.put("eShowType", "Icon");
                                mapDataMore.put("vCategory", generalFunc.retrieveLangLBl("", "LBL_MORE"));
                                //mapDataMore.put("vLogo_image", "" + R.mipmap.ic_more);
                                mapDataMore.put("vLogo_image", MORE_ICON);
                                mapDataMore.put("eShowTerms", "No");
                                mainCategoryList.add(mapDataMore);

                                int mainCategoryListSize = mainCategoryList.size();
                                if (mainCategoryListSize % noColumns != 0) {
                                    mainCategoryList.remove(mainCategoryListSize - 1);
                                    mainCategoryListSize = mainCategoryList.size();
                                    int totCount = 0;

                                    while ((mainCategoryListSize + 1) % noColumns != 0) {
                                        if (mainCategoryListSize >= moreItemListSize) {
                                            break;
                                        }
                                        mainCategoryList.add(moreItemList.get(totCount));
                                        mainCategoryListSize = mainCategoryListSize + 1;
                                        totCount = totCount + 1;
                                    }
                                    if (totCount < moreItemListSize) {
                                        mainCategoryList.add(mapDataMore);
                                    }
                                }
                            }
                            //temp added service list
                            if (serviceList.size() > 0) {
                                mainCategoryList.addAll(serviceList);
                            }
                            if (generalFunc.getJsonValue("RDU_HOME_PAGE_LAYOUT_DESIGN", userProfileJson).equalsIgnoreCase("Banner/Icon")) {
                                mainCategoryList.addAll(0, bannerList);
                            } else {
                                mainCategoryList.addAll(bannerList);
                            }


                        }

                        generalCategoryList.addAll(mainCategoryList);

                        if (isAppType) {
                            int genCatSize = generalCategoryList.size();
                            for (int i = 0; i < genCatSize; i++) {
                                String eShowType = generalCategoryList.get(i).get("eShowType");
                                generalCategoryIconTypeDataMap.put("" + i, "" + (eShowType == null ? "" : eShowType));
                            }
                        }
                    } else {
                        generalCategoryList.addAll(subCategoryList);
                    }

                    ufxCatAdapter = null;
                    ufxCatAdapter = new UberXCategoryAdapter(getActContext(), generalCategoryList, generalFunc);

                    if (!CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                    } else {
                        dataListRecyclerView.removeItemDecoration(itemDecoration);
                    }

                    ufxCatAdapter.setCategoryMode(CAT_TYPE_MODE);
                    dataListRecyclerView.setAdapter(ufxCatAdapter);
                    dataListRecyclerView.getRecycledViewPool().clear();
                    ufxCatAdapter.notifyDataSetChanged();
                    ufxCatAdapter.setOnItemClickList(this);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message", responseObj)));
                }

            } else {
                generalFunc.showError();
            }

            SkeletonViewHandler.getInstance().hideSkeletonView();
            serviceLoadingProgressBar.setVisibility(View.GONE);
        });
        exeWebServer.setCancelAble(false);
    }

    public static float dpToPixels(int dp, Context context) {
        return dp * (context.getResources().getDisplayMetrics().density);
    }

    @Override
    public void onItemClick(int position) {
        if (headerLocAddressTxt.getText().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("Enter Location...", "LBL_ENTER_LOC_HINT_TXT"))) {
            openSourceLocationView();
            return;
        }
        onItemClickHandle(position, "HOME");
    }

    @Override
    public void onMultiItem(String id, boolean b) {

        if (multiServiceSelect.contains(id)) {
            if (!b) {
                while (multiServiceSelect.remove(id)) {
                }
            }

        } else {
            multiServiceSelect.add(id);
        }
    }

    HashMap<String, String> mapData = null;

    public void onItemClickHandle(int position, String type) {


        Utils.hideKeyboard(getActContext());
        mapData = null;

        if (type.equalsIgnoreCase("HOME")) {
            mapData = generalCategoryList.get(position);
        } else {
            mapData = allMainCategoryList.get(position);
        }
        String eCatType = mapData.get("eCatType");
        if (eCatType != null) {
            String s = eCatType.toUpperCase(Locale.US);
            if ("MORE".equals(s)) {

                openMoreDialog();
                return;
            }

            mapData.put("latitude", latitude);
            mapData.put("longitude", longitude);
            mapData.put("address", headerLocAddressTxt.getText().toString());
            (new OpenCatType(getActContext(), mapData)).execute();
        }

        if (eCatType != null && eCatType.equalsIgnoreCase("ServiceProvider")) {
            if (CAT_TYPE_MODE.equals("0")) {
                setSubCategoryList(mapData);
                return;
            }

            if (latitude.equalsIgnoreCase("0.0") || longitude.equalsIgnoreCase("0.0")) {
                generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
            } else {
                checkServiceAvailableOrNot(mapData.get("iVehicleCategoryId"), latitude, longitude, position);
            }
        }

    }

    public void setMainCategory() {
        selectServiceTxt.setText(generalFunc.retrieveLangLBl("Select Service", "LBL_RDU_SERVICES_TITLE"));
        CAT_TYPE_MODE = "0";
        btnArea.setVisibility(View.GONE);

        ((CollapsingToolbarLayout) view.findViewById(R.id.collapsing_toolbar)).setLayoutTransition(new LayoutTransition());
        generalCategoryList.clear();
        scrollAppBarLayout();

        generalCategoryList.addAll(mainCategoryList);
        ufxCatAdapter = null;
        ufxCatAdapter = new UberXCategoryAdapter(getActContext(), generalCategoryList, generalFunc);

        ufxCatAdapter.setCategoryMode("0");

        ufxCatAdapter.setOnItemClickList(this);
        dataListRecyclerView.setAdapter(ufxCatAdapter);

        boolean isRideDeliveryUberx = ServiceModule.RideDeliveryUbexProduct;
        if (isRideDeliveryUberx || (ServiceModule.isServiceProviderOnly())) {
            if (isRideDeliveryUberx) {
                uberXHeaderLayout.setVisibility(View.VISIBLE);
            }

            if (multiServiceSelect != null) {
                multiServiceSelect.clear();
            }
        } else {
            bannerArea.setVisibility(View.VISIBLE);


            uberXHeaderLayout.setVisibility(View.VISIBLE);
        }


        setParentCategoryLayoutManager();

        dataListRecyclerView.removeItemDecoration(itemDecoration);
        dataListRecyclerView.getRecycledViewPool().clear();
        ufxCatAdapter.notifyDataSetChanged();

        if (isbanner) {
            bannerArea.setVisibility(View.VISIBLE);
        }

    }

    public void setSubCategoryList(HashMap<String, String> dataItem) {
        manageMainSubCatShimmer();

        ufxCatAdapter.setCategoryMode("1");
        uberXHeaderLayout.setVisibility(View.VISIBLE);
        CAT_TYPE_MODE = "1";
        dataListRecyclerView.setLayoutManager(new LinearLayoutManager(getActContext()));
        if (UBERX_PARENT_CAT_ID.equalsIgnoreCase("0")) {
            selectServiceTxt.setText(dataItem.get("vCategory"));
        }
        String iVehicleCategoryId = dataItem.get("iVehicleCategoryId");
        Logger.e("geCtaegory", "::setSubCategoryList");
        bannerArea.setVisibility(View.GONE);

        if (generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", obj_userProfile).equalsIgnoreCase("PROVIDER")) {
            btnArea.setVisibility(View.VISIBLE);
        }

        getCategory(iVehicleCategoryId, "1");
        isLoading = true;

    }

    public void scrollAppBarLayout() {
        CoordinatorLayout.LayoutParams params = (CoordinatorLayout.LayoutParams) appBarLayout.getLayoutParams();
        appBarLayout.setLayoutParams(params);
        appBarLayout.setExpanded(true);
    }

    @Override
    public void onResume() {
        super.onResume();
        initializeLocationCheckDone();
        if (generalFunc.prefHasKey(Utils.iServiceId_KEY) && generalFunc != null /*&& !ServiceModule.DeliverAllProduct*/) {
            generalFunc.removeValue(Utils.iServiceId_KEY);
        }

        try {
            if (generalFunc.retrieveValue(Utils.ISWALLETBALNCECHANGE).equalsIgnoreCase("Yes")) {
                getWalletBalDetails();
            } else {
                setUserProfileData("", false);
                setUserInfo();
            }


        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    @Override
    public void onPause() {
        super.onPause();
    }

    @Override
    public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

    }

    @Override
    public void onPageSelected(int i) {

    }

    @Override
    public void onPageScrollStateChanged(int state) {

    }

    public void getWalletBalDetails() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "GetMemberWalletBalance");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {


            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    try {
                        generalFunc.storeData(Utils.ISWALLETBALNCECHANGE, "No");
                        String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                        JSONObject object = generalFunc.getJsonObject(userProfileJson);
                        object.put("user_available_balance", generalFunc.getJsonValue("MemberBalance", responseString));
                        generalFunc.storeData(Utils.USER_PROFILE_JSON, object.toString());

                        setUserProfileData(userProfileJson, true);
                        setUserInfo();
                    } catch (Exception e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                }
            }
        });
    }

    @SuppressLint("SetTextI18n")
    public void setUserInfo() {
        View view = ((Activity) getActContext()).findViewById(android.R.id.content);
        ((MTextView) view.findViewById(R.id.userNameTxt)).setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " "
                + generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        ((MTextView) view.findViewById(R.id.walletbalncetxt)).setText(generalFunc.retrieveLangLBl("", "LBL_WALLET_BALANCE") + ": " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("user_available_balance", obj_userProfile)));


        String url = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImgName", userProfileJson);
        generalFunc.checkProfileImage((SelectableRoundedImageView) view.findViewById(R.id.userImgView), url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);

    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        if (isback) {
            if (getArguments() != null && getArguments().getString("address") != null) {
                return;
            }
        }
        if (getArguments() != null && mAddress != null) {
            address = mAddress;
            isUfxaddress = true;
            headerLocAddressTxt.setText(address);
            this.latitude = getArguments().getString("uberXlat");
            this.longitude = getArguments().getString("uberXlong");
        } else {
            if (address != null && !address.equals("")) {
                isUfxaddress = true;
                this.latitude = latitude + "";
                this.longitude = longitude + "";
                headerLocAddressTxt.setText(address);
                if (noSourceLocationdialog != null) {
                    noSourceLocationdialog.dismiss();
                }
            }
        }
        uberXHomeActivity.mSelectedAddress = address;
    }

    AppCompatDialog noSourceLocationdialog;

    private void openSourceLocationView() {
        if (noSourceLocationdialog != null) {
            noSourceLocationdialog.dismiss();
            noSourceLocationdialog = null;
        }
        noSourceLocationdialog = new AppCompatDialog(getActContext(), android.R.style.Theme_Translucent_NoTitleBar);
        noSourceLocationdialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        noSourceLocationdialog.getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);
        noSourceLocationdialog.setContentView(R.layout.no_source_location_design);
        Objects.requireNonNull(noSourceLocationdialog.getWindow()).getAttributes().windowAnimations = R.style.DialogAnimation;
        noSourceLocationdialog.setCancelable(false);

        ((RippleBackground) noSourceLocationdialog.findViewById(R.id.rippleBgView)).startRippleAnimation();

        ImageView closeImage = noSourceLocationdialog.findViewById(R.id.closeImage);
        closeImage.setOnClickListener(v -> {
            noSourceLocationdialog.dismiss();
        });
        MTextView locationHintText = noSourceLocationdialog.findViewById(R.id.locationHintText);
        MTextView locationDescText = noSourceLocationdialog.findViewById(R.id.locationDescText);
        MTextView btnTxt = noSourceLocationdialog.findViewById(R.id.btnTxt);
        ImageView btnImg = noSourceLocationdialog.findViewById(R.id.btnImg);
        LinearLayout btnArea = noSourceLocationdialog.findViewById(R.id.btnArea);

        DisplayMetrics displayMetrics = new DisplayMetrics();
        getActivity().getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
        int height = displayMetrics.heightPixels;

        RippleBackground.LayoutParams buttonLayoutParams = new RippleBackground.LayoutParams(RippleBackground.LayoutParams.MATCH_PARENT, RippleBackground.LayoutParams.MATCH_PARENT);
        buttonLayoutParams.setMargins(0, 0, 0, -(height / 2));
        ((RippleBackground) noSourceLocationdialog.findViewById(R.id.rippleBgView)).setLayoutParams(buttonLayoutParams);


        if (generalFunc.isRTLmode()) {
            btnImg.setRotation(180);
            btnArea.setBackground(getActContext().getResources().getDrawable(R.drawable.login_border_rtl));
        }

        btnTxt.setText(generalFunc.retrieveLangLBl("ENTER", "LBL_ADD_ADDRESS_TXT"));
        locationDescText.setText(generalFunc.retrieveLangLBl("Please wait while we are trying to access your location. meanwhile you can enter your source location.", "LBL_FETCHING_LOCATION_NOTE_TEXT"));
        locationHintText.setText(generalFunc.retrieveLangLBl("Location", "LBL_LOCATION_FOR_FRONT"));

        btnArea.setOnClickListener(v -> {
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            if (!latitude.equalsIgnoreCase("0.0") && !longitude.equalsIgnoreCase("0.0")) {
                bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, latitude));
                bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, longitude));
            }
            bn.putString("address", headerLocAddressTxt.getText().toString() + "");

            new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class,
                    bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);

        });

        noSourceLocationdialog.show();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == Utils.MY_PROFILE_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            setUserProfileData("", false);
        } else if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {

            headerLocAddressTxt.setText(data.getStringExtra("Address"));
            this.latitude = data.getStringExtra("Latitude") == null ? "0.0" : data.getStringExtra("Latitude");
            this.longitude = data.getStringExtra("Longitude") == null ? "0.0" : data.getStringExtra("Longitude");

            if (!this.latitude.equalsIgnoreCase("0.0") && !this.longitude.equalsIgnoreCase("0.0")) {
                isUfxaddress = true;
            }
            uberXHomeActivity.mSelectedAddress = data.getStringExtra("Address");
            uberXHomeActivity.mSelectedLatitude = latitude;
            uberXHomeActivity.mSelectedLongitude = longitude;

            if (uberXHomeActivity.ENABLE_LOCATION_WISE_BANNER.equalsIgnoreCase("Yes") || isUfxaddress) {
                if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                    manageMainCatShimmer();
                    getCategory(UBERX_PARENT_CAT_ID, CAT_TYPE_MODE);
                } else {
                    manageMainSubCatShimmer();
                    if (mapData == null) {
                        getCategory(UBERX_PARENT_CAT_ID, "1");
                    } else {
                        String iVehicleCategoryId = mapData.get("iVehicleCategoryId");
                        getCategory(iVehicleCategoryId, "1");
                    }
                }
            }
        } else if (requestCode == Utils.CARD_PAYMENT_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            setUserProfileData("", false);
        } else if (requestCode == Utils.SEARCH_PICKUP_LOC_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            isUfxaddress = true;
            headerLocAddressTxt.setText(data.getStringExtra("Address"));
            this.latitude = data.getStringExtra("Latitude");
            this.longitude = data.getStringExtra("Longitude");


        } else if (requestCode == Utils.VERIFY_MOBILE_REQ_CODE) {
            ViewGroup viewGroup = view.findViewById(R.id.MainArea);
            OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
        }
    }

    @Override
    public void onDestroy() {
        super.onDestroy();

        stopLocationUpdates();
        releaseResources();
    }

    public void configCategoryView() {
        setMainCategory();
    }

    public void releaseResources() {
        if (getAddressFromLocation != null) {
            getAddressFromLocation.setAddressList(null);
            getAddressFromLocation = null;
        }
    }

    @Override
    public void onLocationUpdate(Location mLastLocation) {
        stopLocationUpdates();
        latitude = mLastLocation.getLatitude() + "";
        longitude = mLastLocation.getLongitude() + "";
        uberXHomeActivity.mSelectedLatitude = latitude;
        uberXHomeActivity.mSelectedLongitude = longitude;
        isUfxaddress = true;
        if (getAddressFromLocation == null) {
            getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
            getAddressFromLocation.setLocation(mLastLocation.getLatitude(), mLastLocation.getLongitude());
            getAddressFromLocation.setAddressList(this);
            getAddressFromLocation.execute();
            if (uberXHomeActivity.ENABLE_LOCATION_WISE_BANNER.equalsIgnoreCase("Yes")) {
                getCategory(UBERX_PARENT_CAT_ID, CAT_TYPE_MODE);
            }
        }
    }

    public void checkServiceAvailableOrNot(String iVehicleCategoryId, String latitude, String longitude, int position) {

        HashMap<String, String> parameters = new HashMap<String, String>();

        parameters.put("type", "getServiceCategoryTypes");
        parameters.put("iVehicleCategoryId", iVehicleCategoryId);
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("vLatitude", latitude);
        parameters.put("vLongitude", longitude);
        parameters.put("UserType", Utils.userType);
        parameters.put("eCheck", "Yes");

        final ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseObj);

                if (isDataAvail) {
                    Bundle bundle = new Bundle();
                    bundle.putString("iVehicleCategoryId", generalCategoryList.get(position).get("iVehicleCategoryId"));
                    bundle.putString("vCategoryName", generalCategoryList.get(position).get("vCategory"));
                    bundle.putString("latitude", latitude);
                    bundle.putString("longitude", longitude);
                    bundle.putString("address", headerLocAddressTxt.getText().toString());
                    new ActUtils(getActContext()).startActWithData(UberXSelectServiceActivity.class, bundle);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message", responseObj)));
                }
            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }


    public void onClickView(View view) {
        int i = view.getId();
        Bundle bn = new Bundle();
        if (i == submitBtnId) {
            if (latitude.equalsIgnoreCase("0.0") || longitude.equalsIgnoreCase("0.0")) {
                generalFunc.showMessage(view, generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
            } else {

                String SelectedVehicleTypeId = "";
                if (multiServiceSelect.size() > 0) {

                    SelectedVehicleTypeId = android.text.TextUtils.join(",", multiServiceSelect);
                } else {
                    generalFunc.showMessage(view, generalFunc.retrieveLangLBl("Please Select Service", "LBL_SELECT_SERVICE_TXT"));
                    return;
                }
                Bundle bundle = new Bundle();
                bundle.putBoolean("isufx", true);
                bundle.putString("latitude", latitude);
                bundle.putString("longitude", longitude);
                bundle.putString("address", headerLocAddressTxt.getText().toString());
                bundle.putString("SelectvVehicleType", vParentCategoryName);
                bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
                bundle.putString("parentId", parentId);
                bundle.putBoolean("isCarwash", true);

                new ActUtils(getActContext()).startActWithData(MainActivity.class, bundle);

            }
        }

    }


    private interface BounceAnimListener {
        void onAnimationFinished();
    }

    public class setOnClickLst implements View.OnClickListener {

        @Override
        public void onClick(View v) {
            Utils.hideKeyboard(getActContext());
            int id = v.getId();
            if (id == R.id.uberXHeaderLayout) {
                Bundle bn = new Bundle();
                bn.putString("locationArea", "source");
//                    bn.putBoolean("isaddressview", true);
                if (!latitude.equalsIgnoreCase("0.0") && !longitude.equalsIgnoreCase("0.0")) {
                    bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, latitude));
                    bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, longitude));
                }
                bn.putString("address", headerLocAddressTxt.getText().toString() + "");

                new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class,
                        bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);
            } else if (id == R.id.backImgView) {
                if (CAT_TYPE_MODE.equals("1") && UBERX_PARENT_CAT_ID.equalsIgnoreCase("0") && !isLoading) {
                    isLoading = false;
                    multiServiceSelect.clear();
                    backImgView.setVisibility(View.GONE);
                    manageToolBarAddressView(false);
                    uberXHomeActivity.binding.rduTopArea.setVisibility(View.VISIBLE);

                    MainLayout.setBackgroundColor(color);
                    MainArea.setBackgroundColor(color);
                    bannerArea.setBackgroundColor(color);
                    selectServiceTxt.setBackgroundColor(color);

                    configCategoryView();
                    return;
                }
            }
        }
    }

    public void manageToolBarAddressView(boolean isback) {
        if (isback) {
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            int size2sdp = (int) getResources().getDimension(R.dimen._2sdp);
            int size15sdp = (int) getResources().getDimension(R.dimen._15sdp);
            params.setMargins(generalFunc.isRTLmode() ? size15sdp : size2sdp, 0, generalFunc.isRTLmode() ? size2sdp : size15sdp, 0);
            headerLocAddressTxt.setLayoutParams(params);
        } else {
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            int size20sdp = (int) getResources().getDimension(R.dimen._20sdp);
            int size15sdp = (int) getResources().getDimension(R.dimen._15sdp);
            params.setMargins(generalFunc.isRTLmode() ? size15sdp : size20sdp, 0, generalFunc.isRTLmode() ? size20sdp : size15sdp, 0);
            headerLocAddressTxt.setLayoutParams(params);
        }
    }

    BottomSheetDialog moreDialog;

    public void openMoreDialog() {
        if (moreDialog != null && moreDialog.isShowing()) {
            return;
        }
        moreDialog = new BottomSheetDialog(getActContext());

        View contentView = View.inflate(getActContext(), R.layout.dialog_more, null);
        moreDialog.setContentView(contentView);

        moreDialog.setCancelable(true);
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());

        int bannerHeight = (int) (Utils.getScreenPixelHeight(getActContext()) * 0.40);
        mBehavior.setPeekHeight(bannerHeight);
        mBehavior.setHideable(true);

        MTextView moreTitleTxt = contentView.findViewById(R.id.moreTitleTxt);
        MTextView cancelTxt = contentView.findViewById(R.id.cancelTxt);

        moreTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_SERVICE"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        cancelTxt.setOnClickListener(v -> moreDialog.dismiss());

        RecyclerView dataListRecyclerView_more = contentView.findViewById(R.id.dataListRecyclerView_more);
        dataListRecyclerView_more.setLayoutManager(new GridLayoutManager(getActContext(), noColumns));
        UberXCategoryAdapter ufxCatAdapter = new UberXCategoryAdapter(getActContext(), allMainCategoryList, generalFunc, true);
        dataListRecyclerView_more.setAdapter(ufxCatAdapter);


        ufxCatAdapter.setOnItemClickList(new UberXCategoryAdapter.OnItemClickList() {
            @Override
            public void onItemClick(int position) {
                moreDialog.cancel();
                onItemClickHandle(position, "MORE");
            }

            @Override
            public void onMultiItem(String id, boolean b) {

            }
        });

        LayoutDirection.setLayoutDirection(moreDialog);
        moreDialog.show();
    }

    private Integer getNumOfColumns() {
        try {
            DisplayMetrics displayMetrics = getActContext().getResources().getDisplayMetrics();
            int margin_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._10sdp) * 2;
            int menuItem_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._5sdp) * 2;
            int catWidth_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen.category_grid_size);
            int screenWidth_int_value = displayMetrics.widthPixels - margin_int_value - menuItem_int_value;
            return (int) (screenWidth_int_value / catWidth_int_value);
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
        return -1;
    }
}