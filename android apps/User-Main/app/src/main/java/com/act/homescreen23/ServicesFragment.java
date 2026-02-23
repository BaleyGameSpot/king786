package com.act.homescreen23;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.act.MainActivity;
import com.act.UberXHomeActivity;
import com.act.homescreen23.adapter.ServicesAdapter;
import com.fragments.BaseFragment;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.showTermsDialog;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRideDeliveryServicesBinding;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.CommonUtilities;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class ServicesFragment extends BaseFragment {
    private FragmentRideDeliveryServicesBinding binding;
    private UberXHomeActivity mActivity;
    private GeneralFunctions generalFunc;
    private ServicesAdapter servicesAdapter;
    private JSONArray serviceDataArray = new JSONArray();
    private ServerTask currentCallExeWebServer;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_delivery_services, container, false);

        generalFunc = mActivity.generalFunc;

        initializeView();
        serviceList();
        getServiceCategories();

        return binding.getRoot();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof UberXHomeActivity) {
            mActivity = (UberXHomeActivity) requireActivity();
        }
    }

    private void initializeView() {
        ImageView backImgView = binding.getRoot().findViewById(R.id.backImgView);
        backImgView.setVisibility(View.GONE);
        MTextView titleTxt = binding.getRoot().findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICES"));
    }

    private void serviceList() {
        if (servicesAdapter == null) {
            servicesAdapter = new ServicesAdapter(mActivity, serviceDataArray, new ServicesAdapter.OnClickListener() {
                @Override
                public void onServiceBannerItemClick(int position, JSONObject dataObject) {
                    if (dataObject.has("servicesArr")) {
                        JSONArray servicesArr = mActivity.generalFunc.getJsonArray("servicesArr", dataObject);
                        if (servicesArr != null && servicesArr.length() > 0) {
                            onItemClickHandle(position, generalFunc.getJsonObject(servicesArr, 0));
                        }
                    } else {
                        onItemClickHandle(position, dataObject);
                    }
                }

                @Override
                public void onSeeAllClick(int position, JSONObject itemObject) {
                    mActivity.addBottomBarNew.manualClickView(1);
                }
            });
        }
        binding.rvDynamicServicesList.setAdapter(servicesAdapter);
    }

    private void reDirectAction(boolean isWhereTo, boolean isShowSchedule, boolean isAddStop) {
        if (mActivity.mSelectedLatitude.equalsIgnoreCase("0.0") || mActivity.mSelectedLongitude.equalsIgnoreCase("0.0")) {
            generalFunc.showMessage(binding.dataArea, generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
            return;
        }
        Bundle bn = new Bundle();
        bn.putString("selType", Utils.CabGeneralType_Ride);
        bn.putBoolean("isRestart", false);

        bn.putBoolean("isWhereTo", isWhereTo);
        bn.putBoolean("isShowSchedule", isShowSchedule);
        bn.putBoolean("isAddStop", isAddStop);

        bn.putString("address", Utils.checkText(mActivity.mSelectedAddress) ? mActivity.mSelectedAddress : "");

        bn.putString("latitude", Utils.checkText(mActivity.mSelectedLatitude) ? mActivity.mSelectedLatitude : "");
        bn.putString("lat", Utils.checkText(mActivity.mSelectedLatitude) ? mActivity.mSelectedLatitude : "");

        bn.putString("longitude", Utils.checkText(mActivity.mSelectedLongitude) ? mActivity.mSelectedLongitude : "");
        bn.putString("long", Utils.checkText(mActivity.mSelectedLongitude) ? mActivity.mSelectedLongitude : "");

        new ActUtils(mActivity).startActWithData(MainActivity.class, bn);
    }

    private void onItemClickHandle(int position, JSONObject dataObject) {
        if (mActivity.mSelectedLatitude.equalsIgnoreCase("0.0") || mActivity.mSelectedLongitude.equalsIgnoreCase("0.0")) {
            generalFunc.showMessage(binding.dataArea, generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
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
            reDirectAction(false, true, false);
            return;
        } else if (eCatType.equalsIgnoreCase("AddStop")) {
            reDirectAction(true, false, true);
            return;
        }

        if (eCatType.equalsIgnoreCase("ServiceProvider") || eCatType.equalsIgnoreCase("Bidding")) {

            if (generalFunc.getJsonValueStr("eForMedicalService", dataObject).equalsIgnoreCase("Yes")) {
                OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);

            } else if (eCatType.equalsIgnoreCase("Bidding") && generalFunc.getJsonValueStr("other", dataObject).equalsIgnoreCase("Yes")) {
                OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);
            }

        } else {
            OpenCatType23Pro.getInstance().initiateData(mActivity, generalFunc, dataObject, mActivity.mSelectedLatitude, mActivity.mSelectedLongitude, mActivity.mSelectedAddress);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        OpenCatType23Pro.getInstance().clickEvent(false);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getServiceCategories() {
        binding.errorView.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getAllServiceCategories");

        if (currentCallExeWebServer != null) {
            currentCallExeWebServer.cancel(true);
            currentCallExeWebServer = null;
        }
        SkeletonViewHandler.getInstance().hideSkeletonView();
        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(binding.dataArea, R.layout.shimmer_service_screen_23);

        currentCallExeWebServer = ApiHandler.execute(mActivity, parameters, responseString -> {
            SkeletonViewHandler.getInstance().hideSkeletonView();
            currentCallExeWebServer = null;
            if (Utils.checkText(responseString)) {
                serviceDataArray = generalFunc.getJsonArray("SERVICES_SCREEN_DATA", responseString);
                servicesAdapter.updateData(serviceDataArray);
            } else {
                generateErrorView();
            }
        });
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setVisibility(View.VISIBLE);
        binding.errorView.setOnRetryListener(() -> {
            servicesAdapter.updateData(null);
            getServiceCategories();
        });
    }

}