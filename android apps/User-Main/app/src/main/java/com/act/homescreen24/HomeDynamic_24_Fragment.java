package com.act.homescreen24;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatDialog;
import androidx.appcompat.content.res.AppCompatResources;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.CommonIntroActivity;
import com.act.InformationActivity;
import com.act.MainActivity;
import com.act.MyProfileActivity;
import com.act.MyWalletActivity;
import com.act.RequestBidInfoActivity;
import com.act.SearchLocationActivity;
import com.act.SearchServiceActivity;
import com.act.UberXHomeActivity;
import com.act.deliverAll.EditCartActivity;
import com.act.giftcard.GiftCardSendActivity;
import com.act.homescreen23.OpenCatType23Pro;
import com.act.homescreen23.UFXServices23ProView;
import com.act.homescreen23.adapter.HomeUtils;
import com.act.homescreen23.adapter.Main23AdapterNew;
import com.act.homescreen23.adapter.MoreService23Adapter;
import com.act.rentItem.RentItemNewPostActivity;
import com.dialogs.OpenListView;
import com.fragments.BaseFragment;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.GetSubCategoryDataAllCategoryType;
import com.general.files.MyApp;
import com.general.files.OpenNoLocationView;
import com.general.files.showTermsDialog;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.google.gson.Gson;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityUberXhome24Binding;
import com.buddyverse.main.databinding.DialogMore23ProBinding;
import com.map.models.LatLng;
import com.model.ServiceModule;
import com.model.Stop_Over_Points_Data;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.skyfishjy.library.RippleBackground;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class HomeDynamic_24_Fragment extends BaseFragment implements GetLocationUpdates.LocationUpdates, GetAddressFromLocation.AddressFound {

    private static final int UBER_X_SEARCH_SERVICE_REQ_CODE = 201;
    public ActivityUberXhome24Binding binding;
    private UberXHomeActivity mActivity;
    private GeneralFunctions generalFunc;
    private JSONObject mUserProfileObj;

    private AppCompatDialog noSourceLocationDialog;
    private BottomSheetDialog moreDialog;
    private MoreService23Adapter moreS23Adapter;

    public ServerTask currentCallExeWebServer;
    private JSONArray homeScreenDataArray = new JSONArray();
    private Main23AdapterNew main23Adapter;

    private GetLocationUpdates getLastLocation;
    private GetAddressFromLocation getAddressFromLocation;

    public String CAT_TYPE_MODE = "0";
    private String UBERX_PARENT_CAT_ID = "";
    public boolean isUfxAddress = false;
    public UFXServices23ProView mUFXServices23ProView;

    private final ActivityResultLauncher<Intent> someActivityResultLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    JSONObject dataObject = null;
                    try {
                        dataObject = new JSONObject(data.getStringExtra("serviceDataObject"));
                    } catch (JSONException e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                    if (dataObject != null) {
                        onItemClickHandle(0, dataObject);
                    }
                }
            });
    private final ActivityResultLauncher<Intent> introActivity = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    JSONObject dataObject;
                    try {
                        dataObject = new JSONObject(data.getStringExtra("dataObject"));
                        OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);
                    } catch (JSONException e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                }
            });
    private ArrayList<HashMap<String, String>> newListingArrayList;
    public String LBL_LOCATING_YOU_TXT;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        if (binding != null && currentCallExeWebServer == null && mActivity.bottomBtnPos != 1) {
            if (Utils.checkText(Utils.getText(binding.headerAddressTxt))) {
                return binding.getRoot();
            }
        }
        binding = DataBindingUtil.inflate(inflater, R.layout.activity_uber_xhome_24, container, false);
        newListingArrayList = new ArrayList<>();

        if (Utils.checkText(mActivity.mSelectedLatitude) && !mActivity.mSelectedLatitude.equalsIgnoreCase("0.0")
                && Utils.checkText(mActivity.mSelectedLongitude) && !mActivity.mSelectedLongitude.equalsIgnoreCase("0.0")) {
            isUfxAddress = true;
            binding.headerAddressTxt.setText(mActivity.mSelectedAddress);
            binding.UFX23ProArea.headerAddressTxt.setText(mActivity.mSelectedAddress);
            binding.UFX23BuySellRentOnlyArea.headerAddressTxt.setText(mActivity.mSelectedAddress);
        } else {
            isUfxAddress = false;
        }

        if (generalFunc.isDeliverOnlyEnabled() && ServiceModule.DeliverAll) {
            if (!isUfxAddress) {
                generalFunc.isLocationPermissionGranted(true);
            }
        } else {
            if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                    generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
                if (!isUfxAddress) {
                    generalFunc.isLocationPermissionGranted(true);
                }
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////
        initializeView();

        if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
            if (Utils.checkText(generalFunc.retrieveValue("SERVICE_HOME_DATA_23"))) {
                manageHomeScreenView(generalFunc.retrieveValue("SERVICE_HOME_DATA_23"));
            } else {
                getCategory(true);
            }
        }
        binding.swipeRefresh.setOnRefreshListener(() -> getCategory(true));
        binding.UFX23BuySellRentOnlyArea.swipeRefresh.setOnRefreshListener(() -> getCategory(true));
        return binding.getRoot();
    }

    private void initializeView() {
        LBL_LOCATING_YOU_TXT = generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT");
        binding.headerAddressTxt.setHint(LBL_LOCATING_YOU_TXT);
        binding.UFX23ProArea.headerAddressTxt.setHint(LBL_LOCATING_YOU_TXT);
        binding.UFX23BuySellRentOnlyArea.headerAddressTxt.setHint(LBL_LOCATING_YOU_TXT);
        binding.headerAddressView.setOnClickListener(new setOnClickLst());
        binding.userImgView.setOnClickListener(new setOnClickLst());
        binding.userProfileView.setOnClickListener(new setOnClickLst());

        // UFX View
        binding.UFX23ProArea.backImgView.setOnClickListener(new setOnClickLst());
        binding.UFX23ProArea.headerAddressTxt.setOnClickListener(new setOnClickLst());
        binding.UFX23BuySellRentOnlyArea.headerAddressTxt.setOnClickListener(new setOnClickLst());
        if (generalFunc.isRTLmode()) {
            binding.UFX23ProArea.backImgView.setRotation(180);
        }

        // TODO : Service module wish Show UI
        binding.Main23ProArea.setVisibility(View.GONE);
        binding.UFX23ProArea.getRoot().setVisibility(View.GONE);
        binding.UFX23BuySellRentOnlyArea.getRoot().setVisibility(View.GONE);

        if (ServiceModule.isOnlyBuySellRentEnable()) {
            binding.UFX23BuySellRentOnlyArea.getRoot().setVisibility(View.VISIBLE);
        } else if (ServiceModule.isServiceProviderOnly()) {
            onlyServiceProvider();

        } else {
            binding.Main23ProArea.setVisibility(View.VISIBLE);
        }
    }

    private void onlyServiceProvider() {
        if (UBERX_PARENT_CAT_ID.equalsIgnoreCase("0") || UBERX_PARENT_CAT_ID.equalsIgnoreCase("")) {
            binding.Main23ProArea.setVisibility(View.VISIBLE);
            CAT_TYPE_MODE = "0";
        } else {
            CAT_TYPE_MODE = "1";
            binding.UFX23ProArea.getRoot().setVisibility(View.VISIBLE);
            binding.UFX23ProArea.backImgView.setVisibility(View.GONE);

            try {
                JSONObject dataObject = new JSONObject();
                dataObject.put("iParentId", UBERX_PARENT_CAT_ID);
                dataObject.put("iVehicleCategoryId", UBERX_PARENT_CAT_ID);
                setSubCategoryList(dataObject);
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

        }
    }

    @SuppressLint("SetTextI18n")
    private void setInitToolbarArea() {
        String url = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImgName", mUserProfileObj);
        generalFunc.checkProfileImage(binding.userImgView, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
        binding.welcomeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WELCOME_TXT"));
        binding.userNameTxt.setText(generalFunc.getJsonValueStr("vName", mUserProfileObj) + " " + generalFunc.getJsonValueStr("vLastName", mUserProfileObj));
        //
        if (Utils.checkText(generalFunc.getMemberId())) {
            binding.welcomeTxt.setVisibility(View.VISIBLE);
            binding.userImgView.setVisibility(View.VISIBLE);
        } else {
            binding.welcomeTxt.setVisibility(View.GONE);
            binding.userImgView.setVisibility(View.GONE);
        }
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof UberXHomeActivity activity) {
            mActivity = activity;
            getUserData();
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        OpenCatType23Pro.getInstance().clickEvent(false);
        getUserData();
        setInitToolbarArea();
        initializeLocationCheckDone();
    }

    private void getUserData() {
        generalFunc = mActivity.generalFunc;
        if (generalFunc == null) {
            generalFunc = MyApp.getInstance().getGeneralFun(mActivity);
        }
        String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        if (Utils.checkText(userProfileJson)) {
            mUserProfileObj = generalFunc.getJsonObject(userProfileJson);
        } else {
            mUserProfileObj = mActivity.obj_userProfile;
        }
        UBERX_PARENT_CAT_ID = generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, mUserProfileObj);
    }

    public void initializeLocationCheckDone() {
        if (!mActivity.intCheck.isNetworkConnected() && !mActivity.intCheck.check_int()) {
            OpenNoLocationView.getInstance(mActivity, binding.screen23MainArea).configView(false);
            return;
        }
        if (generalFunc.isLocationPermissionGranted(false) && generalFunc.isLocationEnabled()) {
            if (isUfxAddress) {
                stopLocationUpdates();
                Location temploc = new Location("PickupLoc");

                temploc.setLatitude(Double.parseDouble(mActivity.mSelectedLatitude));
                temploc.setLongitude(Double.parseDouble(mActivity.mSelectedLongitude));
                onLocationUpdate(temploc);
            } else {
                initializeLocation();
            }
        } else if (generalFunc.isLocationPermissionGranted(false) && !generalFunc.isLocationEnabled()) {
            if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                    generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
                OpenNoLocationView.getInstance(mActivity, binding.screen23MainArea).configView(false);
            }
        } else if (isUfxAddress) {
            OpenNoLocationView.getInstance(mActivity, binding.screen23MainArea).configView(false);
        }
    }

    private void initializeLocation() {
        binding.headerAddressTxt.setHint(LBL_LOCATING_YOU_TXT);
        binding.UFX23ProArea.headerAddressTxt.setHint(LBL_LOCATING_YOU_TXT);
        binding.UFX23BuySellRentOnlyArea.headerAddressTxt.setHint(LBL_LOCATING_YOU_TXT);
        stopLocationUpdates();
        GetLocationUpdates.locationResolutionAsked = false;
        getLastLocation = new GetLocationUpdates(mActivity, Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);
    }

    private void stopLocationUpdates() {
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
        }
    }

    @Override
    public void onLocationUpdate(Location mLastLocation) {
        if (mLastLocation.getLatitude() == 0.0) {
            return;
        }
        stopLocationUpdates();
        mActivity.mSelectedLatitude = mLastLocation.getLatitude() + "";
        mActivity.mSelectedLongitude = mLastLocation.getLongitude() + "";
        isUfxAddress = true;
        if (getAddressFromLocation == null) {
            getAddressFromLocation = new GetAddressFromLocation(mActivity, generalFunc);
            getAddressFromLocation.setLocation(mLastLocation.getLatitude(), mLastLocation.getLongitude());
            getAddressFromLocation.setAddressList(this);
            getAddressFromLocation.execute();
            if (mActivity.ENABLE_LOCATION_WISE_BANNER.equalsIgnoreCase("Yes")) {
                if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                    getCategory(false);
                } else {
                    if (mUFXServices23ProView != null) {
                        mUFXServices23ProView.initializeView();
                    }
                }
            }
        }
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        if (Utils.checkText(address)) {
            isUfxAddress = true;
            mActivity.mSelectedLatitude = latitude + "";
            mActivity.mSelectedLongitude = longitude + "";
            binding.headerAddressTxt.setText(address);
            binding.UFX23ProArea.headerAddressTxt.setText(address);
            binding.UFX23BuySellRentOnlyArea.headerAddressTxt.setText(address);
            if (noSourceLocationDialog != null) {
                noSourceLocationDialog.dismiss();
            }
        }
        mActivity.mSelectedAddress = address;
        getPreLoadData();
    }

    private void getPreLoadData() {
        if (ServiceModule.DeliverAll) {
            DeliverAllServiceCategory.getInstance().loadRestaurantsAllData(mActivity.mSelectedLatitude, mActivity.mSelectedLongitude);
        }
        if (ServiceModule.ServiceProvider || ServiceModule.ServiceBid) {
            new GetSubCategoryDataAllCategoryType(MyApp.getInstance().getApplicationContext(), generalFunc, HomeUtils.getBidingAndServiceId(mActivity, homeScreenDataArray), mActivity.mSelectedLatitude, mActivity.mSelectedLongitude);
        }
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        stopLocationUpdates();
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        OpenNoLocationView.getInstance(mActivity, binding.screen23MainArea).configView(false);
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        // super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {

            mActivity.mSelectedAddress = data.getStringExtra("Address");
            mActivity.mSelectedLatitude = data.getStringExtra("Latitude") == null ? "0.0" : data.getStringExtra("Latitude");
            mActivity.mSelectedLongitude = data.getStringExtra("Longitude") == null ? "0.0" : data.getStringExtra("Longitude");

            binding.headerAddressTxt.setText(data.getStringExtra("Address"));
            binding.UFX23ProArea.headerAddressTxt.setText(data.getStringExtra("Address"));
            binding.UFX23BuySellRentOnlyArea.headerAddressTxt.setText(data.getStringExtra("Address"));
            if (Utils.checkText(mActivity.mSelectedLatitude) && !mActivity.mSelectedLatitude.equalsIgnoreCase("0.0")
                    && Utils.checkText(mActivity.mSelectedLongitude) && !mActivity.mSelectedLongitude.equalsIgnoreCase("0.0")) {
                isUfxAddress = true;
            }
            if (mActivity.ENABLE_LOCATION_WISE_BANNER.equalsIgnoreCase("Yes") || isUfxAddress) {
                if (noSourceLocationDialog != null) {
                    noSourceLocationDialog.dismiss();
                    noSourceLocationDialog = null;
                }
                if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                    new Handler(Looper.myLooper()).postDelayed(() -> getCategory(true), 100);
                    getPreLoadData();
                } else {
                    if (mUFXServices23ProView != null) {
                        mUFXServices23ProView.initializeView();
                    }
                }
            }

        } else if (requestCode == UBER_X_SEARCH_SERVICE_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {

            if (data.hasExtra("selectedItem")) {
                HashMap<String, String> mapData = (HashMap<String, String>) data.getSerializableExtra("selectedItem");
                if (mapData == null) {
                    return;
                }

                JSONObject dataObject = new JSONObject();
                MyUtils.createJsonObject(mapData, dataObject);
                onItemClickHandle(0, dataObject);
            }
        } else if (requestCode == Utils.VERIFY_MOBILE_REQ_CODE) {
            OpenNoLocationView.getInstance(mActivity, binding.screen23MainArea).configView(false);
        }
    }

    public void openSourceLocationView() {
        if (noSourceLocationDialog != null) {
            noSourceLocationDialog.dismiss();
            noSourceLocationDialog = null;
        }
        if (getLastLocation != null && getLastLocation.getLastLocation() != null && getAddressFromLocation != null) {
            getAddressFromLocation.setLocation(
                    getLastLocation.getLastLocation().getLatitude(),
                    getLastLocation.getLastLocation().getLongitude()
            );
            getAddressFromLocation.execute();
        } else {
            // Handle null cases appropriately
            Logger.e("LocationError", "Location or Address object is null");
        }

        noSourceLocationDialog = new AppCompatDialog(mActivity, android.R.style.Theme_Translucent_NoTitleBar);
        noSourceLocationDialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        noSourceLocationDialog.getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);
        noSourceLocationDialog.setContentView(R.layout.no_source_location_design);
        Objects.requireNonNull(noSourceLocationDialog.getWindow()).getAttributes().windowAnimations = R.style.DialogAnimation;
        noSourceLocationDialog.setCancelable(false);

        ((RippleBackground) Objects.requireNonNull(noSourceLocationDialog.findViewById(R.id.rippleBgView))).startRippleAnimation();
        ImageView closeImage = noSourceLocationDialog.findViewById(R.id.closeImage);
        assert closeImage != null;
        closeImage.setOnClickListener(v -> {
            noSourceLocationDialog.dismiss();
            mActivity.addBottomBarNew.manualClickView(0);
        });
        MTextView locationHintText = noSourceLocationDialog.findViewById(R.id.locationHintText);
        MTextView locationDescText = noSourceLocationDialog.findViewById(R.id.locationDescText);
        MTextView btnTxt = noSourceLocationDialog.findViewById(R.id.btnTxt);
        ImageView btnImg = noSourceLocationDialog.findViewById(R.id.btnImg);
        LinearLayout btnArea = noSourceLocationDialog.findViewById(R.id.btnArea);

        DisplayMetrics displayMetrics = new DisplayMetrics();
        mActivity.getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
        int height = displayMetrics.heightPixels;

        RippleBackground.LayoutParams buttonLayoutParams = new RippleBackground.LayoutParams(RippleBackground.LayoutParams.MATCH_PARENT, RippleBackground.LayoutParams.MATCH_PARENT);
        buttonLayoutParams.setMargins(0, 0, 0, -(height / 2));
        ((RippleBackground) Objects.requireNonNull(noSourceLocationDialog.findViewById(R.id.rippleBgView))).setLayoutParams(buttonLayoutParams);

        assert btnImg != null;
        assert btnArea != null;
        assert btnTxt != null;
        assert locationDescText != null;
        assert locationHintText != null;

        if (generalFunc.isRTLmode()) {
//            btnImg.setRotation(180);
            btnArea.setBackground(AppCompatResources.getDrawable(mActivity, R.drawable.login_border_rtl));
        }

        btnTxt.setText(generalFunc.retrieveLangLBl("ENTER", "LBL_ADD_ADDRESS_TXT"));
        locationDescText.setText(generalFunc.retrieveLangLBl("Please wait while we are trying to access your location. meanwhile you can enter your source location.", "LBL_FETCHING_LOCATION_NOTE_TEXT"));
        locationHintText.setText(generalFunc.retrieveLangLBl("Location", "LBL_LOCATION_FOR_FRONT"));

        btnArea.setOnClickListener(v -> {
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            if (Utils.checkText(mActivity.mSelectedLatitude) && !mActivity.mSelectedLatitude.equalsIgnoreCase("0.0")
                    && Utils.checkText(mActivity.mSelectedLongitude) && !mActivity.mSelectedLongitude.equalsIgnoreCase("0.0")) {
                bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, mActivity.mSelectedLatitude));
                bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, mActivity.mSelectedLongitude));
            }
            bn.putString("address", binding.headerAddressTxt.getText().toString().trim());

            new ActUtils(mActivity).startActForResult(SearchLocationActivity.class, bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);

        });
        noSourceLocationDialog.show();
    }

    private void openMoreDialog(@Nullable String vCategoryTitle, @NonNull JSONArray moreServicesArr) {
        if (moreDialog != null && moreDialog.isShowing()) {
            return;
        }
        moreDialog = new BottomSheetDialog(mActivity);
        DialogMore23ProBinding binding = DialogMore23ProBinding.inflate(LayoutInflater.from(getContext()));
        View contentView = binding.getRoot();

        moreDialog.setContentView(binding.getRoot());
        moreDialog.setCancelable(false);
        BottomSheetBehavior<View> mBehavior = BottomSheetBehavior.from((View) contentView.getParent());

        if (Utils.checkText(vCategoryTitle)) {
            binding.moreTitleTxt.setText(vCategoryTitle);
        } else {
            binding.moreTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_SERVICE"));
        }
        binding.cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        binding.cancelTxt.setOnClickListener(v -> moreDialog.dismiss());
        binding.closeVideoView.setOnClickListener(v -> moreDialog.dismiss());

        if (moreS23Adapter == null) {
            moreS23Adapter = new MoreService23Adapter(mActivity, generalFunc, moreServicesArr, (morePos, mServiceObject) -> {
                moreDialog.cancel();
                onItemClickHandle(morePos, mServiceObject);
            });
        } else {
            moreS23Adapter.updateData(moreServicesArr);
        }

        try {
            JSONObject moreObject = generalFunc.getJsonObject(moreServicesArr, 0);
            if (moreObject.has("GridView")) {
                binding.rvMoreServices.setLayoutManager(new LinearLayoutManager(mActivity));
                int bannerHeight = (int) (Utils.getScreenPixelHeight(mActivity) - getResources().getDimensionPixelSize(R.dimen._50sdp));
                new Handler(Looper.getMainLooper()).postDelayed(() -> {
                    if (bannerHeight > binding.rvMoreServices.getMeasuredHeight()) {
                        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) binding.rvMoreServices.getLayoutParams();
                        params.height = bannerHeight;
                        binding.rvMoreServices.setLayoutParams(params);
                    }
                }, 20);
                mBehavior.setDraggable(false);
            } else {
                binding.rvMoreServices.setLayoutManager(new GridLayoutManager(mActivity, MyUtils.getNumOfColumns(mActivity)));
            }
        } catch (Exception e) {
            binding.rvMoreServices.setLayoutManager(new GridLayoutManager(mActivity, MyUtils.getNumOfColumns(mActivity)));
        }

        binding.rvMoreServices.setAdapter(moreS23Adapter);

        mBehavior.setState(BottomSheetBehavior.STATE_EXPANDED);
        mBehavior.setHideable(false);
        LayoutDirection.setLayoutDirection(moreDialog);
        moreDialog.show();
    }

    private class setOnClickLst implements View.OnClickListener {
        @SuppressLint("NonConstantResourceId")
        @Override
        public void onClick(View v) {
            Utils.hideKeyboard(mActivity);
            int id = v.getId();
            if (id == R.id.backImgView) {
                if (CAT_TYPE_MODE.equals("1") && UBERX_PARENT_CAT_ID.equalsIgnoreCase("0")) {
                    setMainCategory();
                }
            } else if (id == R.id.headerAddressView || id == R.id.headerAddressTxt) {
                Bundle bn = new Bundle();
                bn.putString("locationArea", "source");
                if (Utils.checkText(mActivity.mSelectedLatitude) && !mActivity.mSelectedLatitude.equalsIgnoreCase("0.0")
                        && Utils.checkText(mActivity.mSelectedLongitude) && !mActivity.mSelectedLongitude.equalsIgnoreCase("0.0")) {
                    bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, mActivity.mSelectedLatitude));
                    bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, mActivity.mSelectedLongitude));
                }
                bn.putString("address", Utils.getText(binding.headerAddressTxt));
                new ActUtils(mActivity).startActForResult(SearchLocationActivity.class, bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);
            } else if (id == R.id.searchArea) {
                new ActUtils(mActivity).startActForResult(SearchServiceActivity.class, UBER_X_SEARCH_SERVICE_REQ_CODE);
            } else if (id == R.id.userImgView) {
                new ActUtils(mActivity).startActForResult(MyProfileActivity.class, Utils.MY_PROFILE_REQ_CODE);
            } else if (id == R.id.userProfileView) {
                mActivity.addBottomBarNew.manualClickView(3);
            }
        }
    }

    @Override
    public void onClickView(View view) {
        Utils.hideKeyboard(mActivity);
    }

    ///////////// ===================================================================================
    private void manageHomeScreenView(String responseString) {
        homeScreenDataArray = generalFunc.getJsonArray("HOME_SCREEN_DATA", responseString);
        if (main23Adapter == null) {
            main23Adapter = new Main23AdapterNew(mActivity, homeScreenDataArray, new Main23AdapterNew.OnClickListener() {
                @Override
                public void onItemClick(int position, JSONObject dataObject) {
                    if (dataObject != null) {
                        if (dataObject.has("moreCategories")) {
                            openMoreDialog(generalFunc.getJsonValueStr("vCategoryTitle", dataObject), mActivity.generalFunc.getJsonArray("moreCategories", dataObject));

                        } else if (dataObject.has("SubCategories")) {
                            openMoreDialog(generalFunc.getJsonValueStr("vCategoryTitle", dataObject), mActivity.generalFunc.getJsonArray("SubCategories", dataObject));

                        } else if (ServiceModule.isDeliveronly() && dataObject.has("DELIVERY_SERVICES")) {
                            openMoreDialog(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_SEND_PARCEL_BTN_TXT"), mActivity.generalFunc.getJsonArray("DELIVERY_SERVICES", dataObject));

                        } else if (dataObject.has("servicesArr")) {
                            JSONArray servicesArr = mActivity.generalFunc.getJsonArray("servicesArr", dataObject);
                            if (servicesArr != null && servicesArr.length() > 0) {
                                if (servicesArr.length() > 1) {
                                    openMoreDialog(generalFunc.getJsonValueStr("vCategoryTitle", dataObject), servicesArr);
                                } else {
                                    onItemClickHandle(position, generalFunc.getJsonObject(servicesArr, 0));
                                }
                            }
                        } else {
                            String action = mActivity.generalFunc.getJsonValueStr("action", dataObject);
                            String eCatType = generalFunc.getJsonValueStr("eCatType", dataObject);

                            if (eCatType.equalsIgnoreCase("AddStop") || eCatType.equalsIgnoreCase("RIDE") &&
                                    (dataObject.has("vSubTitle") && Utils.checkText(generalFunc.getJsonValueStr("vSubTitle", dataObject))) ||
                                    eCatType.equalsIgnoreCase("RIDEPOOL") &&
                                            (dataObject.has("vSubTitle") && Utils.checkText(generalFunc.getJsonValueStr("vSubTitle", dataObject)))) {

                                if (eCatType.equalsIgnoreCase("RIDE") && (!generalFunc.containsKey("IS_RIDE_INFO_OPEN") || generalFunc.retrieveValue("IS_RIDE_INFO_OPEN").equalsIgnoreCase("No"))) {
                                    generalFunc.storeData("IS_RIDE_INFO_OPEN", "Yes");
                                    Intent intent = new Intent(mActivity, InformationActivity.class);
                                    intent.putExtra("serviceDataObject", dataObject.toString());
                                    someActivityResultLauncher.launch(intent);
                                } else if (eCatType.equalsIgnoreCase("RIDEPOOL") && (!generalFunc.containsKey("IS_RIDEPOOL_INFO_OPEN") || generalFunc.retrieveValue("IS_RIDEPOOL_INFO_OPEN").equalsIgnoreCase("No"))) {
                                    generalFunc.storeData("IS_RIDEPOOL_INFO_OPEN", "Yes");
                                    Intent intent = new Intent(mActivity, InformationActivity.class);
                                    intent.putExtra("serviceDataObject", dataObject.toString());
                                    someActivityResultLauncher.launch(intent);
                                } else {
                                    onItemClickHandle(position, dataObject);
                                }

                            } else if (Utils.checkText(action)) {
                                if (action.equalsIgnoreCase("TopUp")) {
                                    Bundle bn = new Bundle();
                                    bn.putBoolean("isAddMoney", true);
                                    new ActUtils(mActivity).startActForResult(MyWalletActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
                                } else if (action.equalsIgnoreCase("SendGiftCard")) {
                                    new ActUtils(mActivity).startAct(GiftCardSendActivity.class);
                                } else if (action.equalsIgnoreCase("Cart")) {
                                    new ActUtils(mActivity).startAct(EditCartActivity.class);
                                }
                            } else {
                                onItemClickHandle(position, dataObject);
                            }
                        }
                    }
                }

                @Override
                public void onSeeAllClick(int position, JSONObject itemObject) {
                    mActivity.addBottomBarNew.manualClickView(1);
                }

                @Override
                public void onWhereToClick(int position, JSONObject jsonObject) {
                    reDirectAction(true, false, false);
                }

                @Override
                public void onNowClick(int position, JSONObject jsonObject) {
                    reDirectAction(false, true, false);
                }

                @Override
                public void onNewListingClick(int position, JSONObject itemObject) {
                    showNewListingDialog(position, itemObject);
                }

                @Override
                public void onSearchAreaClick(int position, JSONObject itemObject) {
                    new ActUtils(mActivity).startActForResult(SearchServiceActivity.class, UBER_X_SEARCH_SERVICE_REQ_CODE);
                }
            });
        }
        main23Adapter.setResponseString(responseString);

        RecyclerView rView = binding.dynamicHomeList23RecyclerView;
        if (ServiceModule.isOnlyBuySellRentEnable()) {
            rView = binding.UFX23BuySellRentOnlyArea.dynamicHomeList23RecyclerView;

        }
        rView.setPadding(0, rView.getPaddingTop(), 0, mActivity.getResources().getDimensionPixelSize(R.dimen._11sdp));
        rView.setAdapter(main23Adapter);
        main23Adapter.updateData(homeScreenDataArray);

    }

    private void showNewListingDialog(int position, JSONObject dataObject) {
        newListingArrayList.clear();
        if (dataObject != null) {

            if (dataObject.has("servicesArr")) {
                JSONArray servicesArr = mActivity.generalFunc.getJsonArray("servicesArr", dataObject);
                if (servicesArr != null && servicesArr.length() > 0) {
                    MyUtils.createArrayListJSONArray(generalFunc, newListingArrayList, servicesArr);
                }

                OpenListView instance = OpenListView.getInstance(mActivity, generalFunc.retrieveLangLBl("", "LBL_SELECT_TYPE"), newListingArrayList, OpenListView.OpenDirection.BOTTOM, false, position1 -> {
                    Bundle bn = new Bundle();
                    bn.putBoolean("isHome", true);
                    bn.putString("eType", newListingArrayList.get(position1).get("eType"));
                    new ActUtils(mActivity).startActWithData(RentItemNewPostActivity.class, bn);
                });
                instance.setArrowImageReplacingCheckmark(true);
                instance.show(-1, "vCategoryName");
            }
        }
    }

    private void reDirectAction(boolean isWhereTo, boolean isShowSchedule, boolean isAddStop) {
        if (binding.headerAddressTxt.getText().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("", "")) || binding.headerAddressTxt.getText().toString().equalsIgnoreCase(LBL_LOCATING_YOU_TXT)) {
            openSourceLocationView();
            return;
        }
        if (isWhereTo) {
            Bundle bn = new Bundle();
            bn.putString("locationArea", "dest");
            bn.putBoolean("isDriverAssigned", false);

            double latitude = GeneralFunctions.parseDoubleValue(0.0, mActivity.mSelectedLatitude);
            double longitude = GeneralFunctions.parseDoubleValue(0.0, mActivity.mSelectedLongitude);
            String address = Utils.getText(binding.headerAddressTxt);

            LatLng pickupPlaceLocation = new LatLng(latitude, longitude);
            bn.putDouble("lat", pickupPlaceLocation.latitude);
            bn.putDouble("long", pickupPlaceLocation.longitude);
            bn.putString("address", address);

            bn.putString("type", Utils.CabGeneralType_Ride);

            Gson gson = new Gson();
            String json = gson.toJson(addOrResetStopOverPoints(latitude, longitude, address, new ArrayList<>()));
            bn.putString("stopOverPointsList", json);
            bn.putString("iscubejekRental", "" + false);
            bn.putString("isRental", "" + false);

            bn.putBoolean("isSchedule", isShowSchedule);
            bn.putBoolean("isWhereTo", isWhereTo);
            bn.putBoolean("isAddStop", isAddStop);

            mActivity.launchActForResult(Utils.SEARCH_DEST_LOC_REQ_CODE, new Intent(mActivity, SearchLocationActivity.class).putExtras(bn), (requestCode, activityResult) -> {
                if (activityResult.getResultCode() == Activity.RESULT_OK && activityResult.getData() != null) {
                    Bundle bundle = activityResult.getData().getExtras();
                    if (bundle != null) {
                        bundle.putString("selType", Utils.CabGeneralType_Ride);
                        bundle.putBoolean("isRestart", false);

                        bundle.putBoolean("isWhereToResponse", true);

                        bundle.putString("address", Utils.getText(binding.headerAddressTxt));
                        bundle.putString("latitude", Utils.checkText(mActivity.mSelectedLatitude) ? mActivity.mSelectedLatitude : "");
                        bundle.putString("lat", Utils.checkText(mActivity.mSelectedLatitude) ? mActivity.mSelectedLatitude : "");
                        bundle.putString("longitude", Utils.checkText(mActivity.mSelectedLongitude) ? mActivity.mSelectedLongitude : "");
                        bundle.putString("long", Utils.checkText(mActivity.mSelectedLongitude) ? mActivity.mSelectedLongitude : "");

                        new ActUtils(mActivity).startActWithData(MainActivity.class, bundle);
                    }
                }
            });
            return;
        }
        Bundle bn = new Bundle();
        bn.putString("selType", Utils.CabGeneralType_Ride);
        bn.putBoolean("isRestart", false);

        bn.putBoolean("isWhereTo", isWhereTo);
        bn.putBoolean("isShowSchedule", isShowSchedule);
        bn.putBoolean("isAddStop", isAddStop);

        bn.putString("address", Utils.getText(binding.headerAddressTxt));

        bn.putString("latitude", Utils.checkText(mActivity.mSelectedLatitude) ? mActivity.mSelectedLatitude : "");
        bn.putString("lat", Utils.checkText(mActivity.mSelectedLatitude) ? mActivity.mSelectedLatitude : "");

        bn.putString("longitude", Utils.checkText(mActivity.mSelectedLongitude) ? mActivity.mSelectedLongitude : "");
        bn.putString("long", Utils.checkText(mActivity.mSelectedLongitude) ? mActivity.mSelectedLongitude : "");

        new ActUtils(mActivity).startActWithData(MainActivity.class, bn);
    }

    private ArrayList<Stop_Over_Points_Data> addOrResetStopOverPoints(Double latitude, Double longitude, String address, ArrayList<Stop_Over_Points_Data> stopOverPointsList) {
        Stop_Over_Points_Data stop_over_points_data = new Stop_Over_Points_Data();
        stop_over_points_data.setDestAddress(address);
        stop_over_points_data.setDestLat(latitude);
        stop_over_points_data.setDestLong(longitude);
        stop_over_points_data.setDestLatLong(new LatLng(latitude, longitude));
        stop_over_points_data.setHintLable(generalFunc.retrieveLangLBl("", "LBL_PICK_UP_FROM"));
        stop_over_points_data.setAddressAdded(true);
        stop_over_points_data.setDestination(false);
        stop_over_points_data.setRemovable(false);
        stopOverPointsList.add(stop_over_points_data);

        Stop_Over_Points_Data stop_over_points_data1 = new Stop_Over_Points_Data();
        stop_over_points_data1.setDestAddress("");
        stop_over_points_data1.setDestLat(null);
        stop_over_points_data1.setDestLong(null);
        stop_over_points_data1.setDestLatLong(null);
        stop_over_points_data1.setHintLable(generalFunc.retrieveLangLBl("", "LBL_DROP_AT"));
        stop_over_points_data1.setAddressAdded(false);
        stop_over_points_data1.setDestination(true);
        stop_over_points_data1.setRemovable(false);
        stopOverPointsList.add(stop_over_points_data1);
        return stopOverPointsList;
    }

    private void onItemClickHandle(int position, JSONObject dataObject) {
        Utils.hideKeyboard(mActivity);
        if (binding.headerAddressTxt.getText().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("", "")) || binding.headerAddressTxt.getText().toString().equalsIgnoreCase(LBL_LOCATING_YOU_TXT)) {
            openSourceLocationView();
            return;
        }

        boolean eShowTerms = generalFunc.getJsonValueStr("eShowTerms", dataObject).equalsIgnoreCase("Yes");
        if (eShowTerms && !CommonUtilities.ageRestrictServices.contains("1")) {
            new showTermsDialog(mActivity, generalFunc, position, generalFunc.getJsonValueStr("vCategory", dataObject), false, () -> {
                //
                onItemClickHandle(position, dataObject);
            });
            return;
        }

        String eCatType = generalFunc.getJsonValueStr("eCatType", dataObject);
        if (eCatType.equalsIgnoreCase("RideReserve")) {
            if (!generalFunc.containsKey("IS_RIDERESERVE_INFO_OPEN") || generalFunc.retrieveValue("IS_RIDERESERVE_INFO_OPEN").equalsIgnoreCase("No") && Utils.checkText(generalFunc.getJsonValueStr("vPageTitle", dataObject))) {
                generalFunc.storeData("IS_RIDERESERVE_INFO_OPEN", "Yes");
                Intent intent = new Intent(mActivity, InformationActivity.class);
                intent.putExtra("serviceDataObject", dataObject.toString());
                someActivityResultLauncher.launch(intent);
            } else {
                reDirectAction(false, true, false);
            }
            return;
        } else if (eCatType.equalsIgnoreCase("AddStop")) {
            if (!generalFunc.containsKey("IS_ADDSTOP_INFO_OPEN") || generalFunc.retrieveValue("IS_ADDSTOP_INFO_OPEN").equalsIgnoreCase("No") && Utils.checkText(generalFunc.getJsonValueStr("vPageTitle", dataObject))) {
                generalFunc.storeData("IS_ADDSTOP_INFO_OPEN", "Yes");
                Intent intent = new Intent(mActivity, InformationActivity.class);
                intent.putExtra("serviceDataObject", dataObject.toString());
                someActivityResultLauncher.launch(intent);
            } else {
                reDirectAction(true, false, true);
            }
            return;
        } else if (eCatType.equalsIgnoreCase("TAXIBID") || eCatType.equalsIgnoreCase("MOTOBID")) {
            String myType = "isTaxiBid";
            if (!Utils.checkText(generalFunc.retrieveValue(myType))) {
                if (!generalFunc.containsKey("IS_BIDDING_INFO_OPEN") || generalFunc.retrieveValue("IS_BIDDING_INFO_OPEN").equalsIgnoreCase("No")) {
                    generalFunc.storeData("IS_BIDDING_INFO_OPEN", "Yes");
                    Intent intent = new Intent(mActivity, CommonIntroActivity.class);
                    Bundle bn = new Bundle();
                    bn.putString("dataObject", dataObject.toString());
                    bn.putString("viewIntroType", myType);
                    intent.putExtras(bn);
                    introActivity.launch(intent);
                    return;
                } else {
                    OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);
                    return;
                }
            }
        }

        if (eCatType.equalsIgnoreCase("ServiceProvider") || eCatType.equalsIgnoreCase("Bidding")) {

            if (generalFunc.getJsonValueStr("eForMedicalService", dataObject).equalsIgnoreCase("Yes")) {
                OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);

            } else if (eCatType.equalsIgnoreCase("Bidding") && generalFunc.getJsonValueStr("other", dataObject).equalsIgnoreCase("Yes")) {
                OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);

            } else {
                setSubCategoryList(dataObject);
            }

        } else {
            OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);
        }
    }

    public void setMainCategory() {
        CAT_TYPE_MODE = "0";
        if (ServiceModule.isServiceProviderOnly()) {
            onlyServiceProvider();

        } else {
            binding.Main23ProArea.setVisibility(View.VISIBLE);
        }
        binding.UFX23ProArea.getRoot().setVisibility(View.GONE);
        setInitToolbarArea();
        mActivity.binding.rduTopArea.setVisibility(View.VISIBLE);
    }

    private void setSubCategoryList(JSONObject dataObject) {
        if (CAT_TYPE_MODE.equalsIgnoreCase("0") &&
                !generalFunc.getJsonValueStr("iParentId", dataObject).equalsIgnoreCase("0")) {
            boolean isVideoConsultEnable = dataObject.has("isVideoConsultEnable") && generalFunc.getJsonValueStr("isVideoConsultEnable", dataObject).equalsIgnoreCase("Yes");
            boolean isBidding = dataObject.has("iBiddingId") && Utils.checkText(generalFunc.getJsonValueStr("iBiddingId", dataObject));
            SubmitButtonClick(generalFunc.getJsonValueStr("iVehicleCategoryId", dataObject), generalFunc.getJsonValueStr("iParentId", dataObject), generalFunc.getJsonValueStr("vCategory", dataObject), isVideoConsultEnable, isBidding);
            return;
        }
        if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
            mActivity.binding.rduTopArea.setVisibility(View.GONE);
        }

        CAT_TYPE_MODE = "1";
        if (mUFXServices23ProView == null) {
            mUFXServices23ProView = new UFXServices23ProView(mActivity, generalFunc, binding, dataObject, new UFXServices23ProView.OnUFXServiceViewListener() {
                @Override
                public void onProcess(boolean isLoadingView) {
                    if (isLoadingView) {
                        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(binding.UFX23ProArea.UFXDataArea, R.layout.subcategory_shimmer_view_22);
                    } else {
                        SkeletonViewHandler.getInstance().hideSkeletonView();
                    }
                }

                @Override
                public void onSubmitButtonClick(String selectedVehicleTypeId, String iParentId, String categoryName, boolean mIsVideoConsultEnable, boolean isBidding) {
                    SubmitButtonClick(selectedVehicleTypeId, iParentId, categoryName, mIsVideoConsultEnable, isBidding);
                }
            });
        } else {
            mUFXServices23ProView.initializeView(dataObject);
        }
    }

    private void SubmitButtonClick(String selectedVehicleTypeId, String iParentId, String categoryName, boolean mIsVideoConsultEnable, boolean isBidding) {
        Bundle bundle = new Bundle();
        bundle.putBoolean("isufx", true);
        bundle.putString("latitude", mActivity.mSelectedLatitude);
        bundle.putString("longitude", mActivity.mSelectedLongitude);
        bundle.putString("address", binding.headerAddressTxt.getText().toString());
        bundle.putString("SelectvVehicleType", binding.UFX23ProArea.selectServiceTxt.getText().toString());

        if (Utils.checkText(selectedVehicleTypeId)) {
            bundle.putString("SelectedVehicleTypeId", selectedVehicleTypeId);
            bundle.putString("parentId", iParentId);
        } else {
            bundle.putString("SelectedVehicleTypeId", iParentId);
            bundle.putString("parentId", iParentId);
        }

        bundle.putBoolean("isCarwash", true);
        bundle.putBoolean("isVideoConsultEnable", mIsVideoConsultEnable);

        if (isBidding) {
            if (Utils.checkText(selectedVehicleTypeId) && Utils.checkText(categoryName)) {
                bundle.putString("SelectvVehicleType", categoryName);
            }
            new ActUtils(mActivity).startActWithData(RequestBidInfoActivity.class, bundle);
        } else {
            new ActUtils(mActivity).startActWithData(MainActivity.class, bundle);
        }
    }

    private void getCategory(boolean isShimmerView) {
        if (isShimmerView) {
            if (binding.UFX23BuySellRentOnlyArea.getRoot().getVisibility() == View.VISIBLE) {
                SkeletonViewHandler.getInstance().ShowNormalSkeletonView(binding.UFX23BuySellRentOnlyArea.dataArea, R.layout.shimmer_home_screen_23);
            } else {
                SkeletonViewHandler.getInstance().ShowNormalSkeletonView(binding.dataArea, R.layout.shimmer_home_screen_23);
            }
            binding.headerAddressTxt.setEnabled(false);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategoriesPro");
        parameters.put("parentId", UBERX_PARENT_CAT_ID);
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("vLatitude", mActivity.mSelectedLatitude);
        parameters.put("vLongitude", mActivity.mSelectedLongitude);

        if (currentCallExeWebServer != null) {
            currentCallExeWebServer.cancel(true);
            currentCallExeWebServer = null;
        }

        currentCallExeWebServer = ApiHandler.execute(mActivity, parameters, responseString -> {
            binding.swipeRefresh.setRefreshing(false);
            binding.UFX23BuySellRentOnlyArea.swipeRefresh.setRefreshing(false);

            currentCallExeWebServer = null;
            manageHomeScreenView(responseString);

            binding.headerAddressTxt.setEnabled(true);
            SkeletonViewHandler.getInstance().hideSkeletonView();

        });
    }
}