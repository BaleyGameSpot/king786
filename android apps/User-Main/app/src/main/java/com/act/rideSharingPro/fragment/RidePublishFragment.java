package com.act.rideSharingPro.fragment;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.rideSharingPro.RideMyDetails;
import com.act.rideSharingPro.RideMyList;
import com.act.rideSharingPro.RideShareActiveTripActivity;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.adapter.RideMyPublishAdapter;
import com.dialogs.OpenListView;
import com.fragments.BaseFragment;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRidePublishBinding;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RidePublishFragment extends BaseFragment {

    private FragmentRidePublishBinding binding;
    private Activity mActivity;
    private GeneralFunctions generalFunc;
    private RideMyPublishAdapter mRideMyPublishAdapter;
    private final ArrayList<HashMap<String, String>> mRideMyList = new ArrayList<>();
    boolean mIsLoading = false, isNextPageAvailable = false;
    private String next_page_str = "1";
    private final ArrayList<HashMap<String, String>> subFilterList = new ArrayList<>();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_publish, container, false);

        binding.loading.setVisibility(View.GONE);
        binding.noDataArea.setVisibility(View.GONE);
        binding.shadowHeaderView.setVisibility(View.INVISIBLE);

        mRideMyPublishAdapter = new RideMyPublishAdapter(generalFunc, mRideMyList, new RideMyPublishAdapter.OnClickListener() {
            @Override
            public void onItemClick(int position, HashMap<String, String> mapData) {
                Bundle bn = new Bundle();
                bn.putSerializable("myRideDataHashMap", mapData);
                new ActUtils(mActivity).startActForResult(RideMyDetails.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
            }

            @Override
            public void onStartTripButtonCall(int position, HashMap<String, String> mapData) {
                if (mapData.containsKey("RideState")) {
                    JSONObject rideState = generalFunc.getJsonObject(mapData.get("RideState"));
                    if (generalFunc.getJsonValueStr("RideState", rideState).equalsIgnoreCase("Start")) {
                        rideStateMarkPickup(mapData);
                    } else if (generalFunc.getJsonValueStr("RideState", rideState).equalsIgnoreCase("MarkAsPickup")) {
                        Bundle bundle = new Bundle();
                        bundle.putString("tripData", rideState.toString());
                        bundle.putString("publishRideId", mapData.get("iPublishedRideId"));
                        bundle.putBoolean("isFromPublishRide", true);
                        new ActUtils(mActivity).startActForResult(RideShareActiveTripActivity.class, bundle, MyUtils.REFRESH_DATA_REQ_CODE);
                        //rideStateMarkPickup(mapData);
                    } else if (generalFunc.getJsonValueStr("RideState", rideState).equalsIgnoreCase("TripEnd")) {
                        Bundle bundle = new Bundle();
                        bundle.putString("tripData", rideState.toString());
                        bundle.putString("publishRideId", mapData.get("iPublishedRideId"));
                        bundle.putBoolean("isFromPublishRide", true);
                        new ActUtils(mActivity).startActForResult(RideShareActiveTripActivity.class, bundle, MyUtils.REFRESH_DATA_REQ_CODE);
                        //rideStateMarkPickup(mapData);
                    } else if (generalFunc.getJsonValueStr("RideState", rideState).equalsIgnoreCase("PaymentCollected")) {
                        Bundle bundle = new Bundle();
                        bundle.putString("tripData", rideState.toString());
                        bundle.putString("publishRideId", mapData.get("iPublishedRideId"));
                        bundle.putBoolean("isFromPublishRide", true);
                        new ActUtils(mActivity).startActForResult(RideShareActiveTripActivity.class, bundle, MyUtils.REFRESH_DATA_REQ_CODE);

                        //paymentCollected(mapData);
                    } else {
                        Bundle bundle = new Bundle();
                        bundle.putString("tripData", rideState.toString());
                        bundle.putString("publishRideId", mapData.get("iPublishedRideId"));
                        bundle.putBoolean("isFromPublishRide", true);
                        new ActUtils(mActivity).startActForResult(RideShareActiveTripActivity.class, bundle, MyUtils.REFRESH_DATA_REQ_CODE);
                        /*Bundle bn = new Bundle();
                        bn.putSerializable("myRideDataHashMap", mapData);
                        new ActUtils(mActivity).startActForResult(RideMyDetails.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);*/
                        //rideStateMarkPickup(mapData);
                    }
                } /*else {
                    startTripGetDetails(mapData);
                }*/
            }
        });
        binding.rvRidePublishList.addItemDecoration(new SpacesItemDecoration(1, getResources().getDimensionPixelSize(R.dimen._12sdp), false));
        binding.rvRidePublishList.setAdapter(mRideMyPublishAdapter);
        binding.rvRidePublishList.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (recyclerView.canScrollVertically(1)) {
                    int visibleItemCount = Objects.requireNonNull(binding.rvRidePublishList.getLayoutManager()).getChildCount();
                    int totalItemCount = binding.rvRidePublishList.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) binding.rvRidePublishList.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if (((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable)) {
                        mIsLoading = true;
                        mRideMyPublishAdapter.addFooterView();
                        binding.rvRidePublishList.stopScroll();

                        String vSubFilterParam = "";
                        if (mActivity instanceof RideMyList activity) {
                            vSubFilterParam = activity.vPublishParam;
                        } else if (mActivity instanceof RideSharingProHomeActivity activity) {
                            vSubFilterParam = activity.rsRidesFragment.vPublishParam;
                        }
                        getPublishRidesList(vSubFilterParam, true);

                    } else if (!isNextPageAvailable) {
                        mRideMyPublishAdapter.removeFooterView();
                    }
                }
                if (!recyclerView.canScrollVertically(-1)) {
                    binding.shadowHeaderView.setVisibility(View.INVISIBLE);
                } else {
                    binding.shadowHeaderView.setVisibility(View.VISIBLE);
                }
            }
        });
        binding.filterArea.setOnClickListener(view -> BuildType());

        getPublishRidesList("", false);

        return binding.getRoot();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof RideMyList activity) {
            mActivity = activity;
            generalFunc = activity.generalFunc;
        } else if (requireActivity() instanceof RideSharingProHomeActivity activity) {
            mActivity = activity;
            generalFunc = activity.generalFunc;
        }
    }

    private void BuildType() {
        OpenListView.getInstance(mActivity, generalFunc.retrieveLangLBl("", "LBL_SELECT_TYPE"), subFilterList, OpenListView.OpenDirection.BOTTOM, true, position -> {

            binding.filterTxt.setText(subFilterList.get(position).get("vTitle"));
            String vSubFilterParam = subFilterList.get(position).get("vSubFilterParam");
            if (mActivity instanceof RideMyList activity) {
                activity.vPublishParam = vSubFilterParam;
            } else if (mActivity instanceof RideSharingProHomeActivity activity) {
                activity.rsRidesFragment.vPublishParam = vSubFilterParam;
            }
            getPublishRidesList(vSubFilterParam, false);
            binding.rvRidePublishList.setVisibility(View.GONE);
            binding.noDataArea.setVisibility(View.GONE);

        }).show(populatePos(), "vTitle");
    }

    private void rideStateMarkPickup(HashMap<String, String> mapData) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "publishRideUpdateState");
        parameters.put("iPublishedRideId", mapData.get("iPublishedRideId"));

        ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    Bundle bundle = new Bundle();
                    bundle.putString("tripData", message);
                    bundle.putString("publishRideId", mapData.get("iPublishedRideId"));
                    bundle.putBoolean("isFromPublishRide", true);
                    new ActUtils(mActivity).startActForResult(RideShareActiveTripActivity.class, bundle, MyUtils.REFRESH_DATA_REQ_CODE);
                } else {
                    if (Utils.checkText(generalFunc.getJsonValue("eIsBookingRidePending", responseString))) {
                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("vTitle", responseString)), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_CONTINUE"), i -> {
                            if (i == 1) {
                                Bundle bn = new Bundle();
                                bn.putSerializable("myRideDataHashMap", mapData);
                                new ActUtils(mActivity).startActForResult(RideMyDetails.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
                            }
                        });
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    @SuppressLint("NotifyDataSetChanged")
    public void getPublishRidesList(String FilterType, boolean isScroll) {
        binding.errorView.setVisibility(View.GONE);
        if (!isScroll) {
            binding.loading.setVisibility(View.VISIBLE);
        }
        if (subFilterList.size() == 0) {
            binding.filterArea.setVisibility(View.GONE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetPublishedRides");
        parameters.put("page", next_page_str);
        if (Utils.checkText(FilterType)) {
            parameters.put("vSubFilterParam", FilterType);
        }

        ApiHandler.execute(mActivity, parameters, responseString -> {
            mIsLoading = false;
            binding.loading.setVisibility(View.GONE);
            binding.noDataArea.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                String nextPage = generalFunc.getJsonValue("NextPage", responseString);
                binding.rvRidePublishList.setVisibility(View.VISIBLE);
                binding.filterArea.setVisibility(View.VISIBLE);

                if (!isScroll) {
                    mRideMyList.clear();
                }
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    JSONArray dataArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    MyUtils.createArrayListJSONArray(generalFunc, mRideMyList, dataArray);
                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                }
                if (mRideMyList.size() == 0) {
                    binding.noDataArea.setVisibility(View.VISIBLE);
                    binding.noDataTitleTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)));
                    binding.noDataMsgTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
                if (Utils.checkText(generalFunc.getJsonValue("vSubFilterParam", responseString))) {
                    binding.filterTxt.setText(generalFunc.getJsonValue("vSubFilterParam", responseString));
                    if (mActivity instanceof RideMyList activity) {
                        activity.vPublishParam = generalFunc.getJsonValue("vSubFilterParam", responseString);
                    } else if (mActivity instanceof RideSharingProHomeActivity activity) {
                        activity.rsRidesFragment.vPublishParam = generalFunc.getJsonValue("vSubFilterParam", responseString);
                    }
                }
                buildFilters(generalFunc.getJsonObject(responseString));
                mRideMyPublishAdapter.notifyDataSetChanged();
            } else {
                if (!isScroll) {
                    removeNextPageConfig();
                    generateErrorView(FilterType, isScroll);
                }
            }
        });
    }

    private void removeNextPageConfig() {
        next_page_str = "1";
        isNextPageAvailable = false;
        mIsLoading = false;
        mRideMyPublishAdapter.removeFooterView();
    }

    private void generateErrorView(String FilterType, boolean isScroll) {
        binding.rvRidePublishList.setVisibility(View.GONE);
        binding.filterArea.setVisibility(View.GONE);

        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setVisibility(View.VISIBLE);
        binding.errorView.setOnRetryListener(() -> getPublishRidesList(FilterType, isScroll));
    }

    private void buildFilters(JSONObject responseObj) {
        if (responseObj == null) return;
        JSONArray subFilterOptionArr = generalFunc.getJsonArray("subFilterOption", responseObj);
        subFilterList.clear();
        if (subFilterOptionArr != null && subFilterOptionArr.length() > 0) {
            for (int i = 0; i < subFilterOptionArr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(subFilterOptionArr, i);
                HashMap<String, String> map = new HashMap<String, String>();
                String vTitle = generalFunc.getJsonValueStr("vTitle", obj_temp);
                map.put("vTitle", vTitle);
                String vSubFilterParam = generalFunc.getJsonValueStr("vSubFilterParam", obj_temp);
                map.put("vSubFilterParam", vSubFilterParam);

                if (mActivity instanceof RideMyList activity) {
                    if (activity.vPublishParam.equalsIgnoreCase(vSubFilterParam)) {

                        activity.vPublishParam = vSubFilterParam;
                        activity.vPublishPos = i;

                        binding.filterTxt.setText(vTitle);
                    }
                } else if (mActivity instanceof RideSharingProHomeActivity activity) {
                    if (activity.rsRidesFragment.vPublishParam.equalsIgnoreCase(vSubFilterParam)) {

                        activity.rsRidesFragment.vPublishParam = vSubFilterParam;
                        activity.rsRidesFragment.vPublishPos = i;

                        binding.filterTxt.setText(vTitle);
                    }
                }
                subFilterList.add(map);
            }
        }
    }

    private int populatePos() {
        if (mActivity instanceof RideMyList activity) {
            return activity.vPublishPos;
        } else if (mActivity instanceof RideSharingProHomeActivity activity) {
            return activity.rsRidesFragment.vPublishPos;
        }
        return -1;
    }
}