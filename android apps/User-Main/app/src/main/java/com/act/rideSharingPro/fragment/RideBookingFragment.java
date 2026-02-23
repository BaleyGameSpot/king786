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

import com.act.ConfirmEmergencyTapActivity;
import com.act.rideSharingPro.RideBookDetails;
import com.act.rideSharingPro.RideMyList;
import com.act.rideSharingPro.RideShareActiveTripActivity;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.adapter.RideBookSearchAdapter;
import com.dialogs.OpenListView;
import com.fragments.BaseFragment;
import com.general.features.SafetyTools;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRideBookingBinding;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RideBookingFragment extends BaseFragment {

    private FragmentRideBookingBinding binding;
    private Activity mActivity;
    private GeneralFunctions generalFunc;
    private JSONObject obj_userProfile;

    private RideBookSearchAdapter mRideBookSearchAdapter;
    private final ArrayList<HashMap<String, String>> mRideBookSearchList = new ArrayList<>();
    boolean mIsLoading = false, isNextPageAvailable = false;
    private String next_page_str = "1";
    private final ArrayList<HashMap<String, String>> subFilterList = new ArrayList<>();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_booking, container, false);

        binding.loading.setVisibility(View.GONE);
        binding.noDataArea.setVisibility(View.GONE);
        binding.shadowHeaderView.setVisibility(View.INVISIBLE);

        mRideBookSearchAdapter = new RideBookSearchAdapter(generalFunc, mRideBookSearchList, new RideBookSearchAdapter.OnClickListener() {
            @Override
            public void onItemClick(int position, HashMap<String, String> mapData) {
                Bundle bn = new Bundle();
                bn.putSerializable("myRideDataHashMap", mapData);
                new ActUtils(mActivity).startActForResult(RideBookDetails.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
            }

            @Override
            public void onTrackDriverBtnClick(int position, HashMap<String, String> mapData) {
                Bundle bundle = new Bundle();
                bundle.putSerializable("mapData", mapData);
                bundle.putBoolean("isFromPublishRide", false);
                new ActUtils(mActivity).startActForResult(RideShareActiveTripActivity.class, bundle, MyUtils.REFRESH_DATA_REQ_CODE);
            }

            @Override
            public void onSOSBtnClick(int position, HashMap<String, String> mapData) {
                if (generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {
                    SafetyTools.getInstance().initiate(mActivity, generalFunc, mapData.get("iBookingId"), "");
                    SafetyTools.getInstance().safetyToolsDialog(false);
                } else {
                    Bundle bn = new Bundle();
                    bn.putString("TripId", mapData.get("iBookingId"));
                    new ActUtils(mActivity).startActWithData(ConfirmEmergencyTapActivity.class, bn);
                }
            }
        });
        mRideBookSearchAdapter.setIsFromSearchScreen(false);
        binding.rvRideBookingList.addItemDecoration(new SpacesItemDecoration(1, getResources().getDimensionPixelSize(R.dimen._12sdp), false));
        binding.rvRideBookingList.setAdapter(mRideBookSearchAdapter);
        binding.rvRideBookingList.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (recyclerView.canScrollVertically(1)) {
                    int visibleItemCount = Objects.requireNonNull(binding.rvRideBookingList.getLayoutManager()).getChildCount();
                    int totalItemCount = binding.rvRideBookingList.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) binding.rvRideBookingList.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if (((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable)) {
                        mIsLoading = true;
                        mRideBookSearchAdapter.addFooterView();
                        binding.rvRideBookingList.stopScroll();

                        String vSubFilterParam = "";
                        if (mActivity instanceof RideMyList activity) {
                            vSubFilterParam = activity.vPublishParam;
                        } else if (mActivity instanceof RideSharingProHomeActivity activity) {
                            vSubFilterParam = activity.rsRidesFragment.vPublishParam;
                        }
                        GetBookingsRidesList(vSubFilterParam, true);

                    } else if (!isNextPageAvailable) {
                        mRideBookSearchAdapter.removeFooterView();
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

        GetBookingsRidesList("", false);

        return binding.getRoot();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof RideMyList activity) {
            mActivity = activity;
            generalFunc = activity.generalFunc;
            obj_userProfile = activity.obj_userProfile;
        } else if (requireActivity() instanceof RideSharingProHomeActivity activity) {
            mActivity = activity;
            generalFunc = activity.generalFunc;
            obj_userProfile = activity.obj_userProfile;
        }

        if (mActivity == null) {
            mActivity = MyApp.getInstance().getCurrentAct();
        }

        if (generalFunc == null) {
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void GetBookingsRidesList(String FilterType, boolean isScroll) {
        binding.errorView.setVisibility(View.GONE);
        if (!isScroll) {
            binding.loading.setVisibility(View.VISIBLE);
        }
        if (subFilterList.size() == 0) {
            binding.filterArea.setVisibility(View.GONE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetBookings");
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
                binding.rvRideBookingList.setVisibility(View.VISIBLE);
                binding.filterArea.setVisibility(View.VISIBLE);

                if (!isScroll) {
                    mRideBookSearchList.clear();
                }
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    JSONArray dataArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    MyUtils.createArrayListJSONArray(generalFunc, mRideBookSearchList, dataArray);
                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                }
                if (mRideBookSearchList.size() == 0) {
                    binding.noDataArea.setVisibility(View.VISIBLE);
                    binding.noDataTitleTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)));
                    binding.noDataMsgTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
                if (Utils.checkText(generalFunc.getJsonValue("vSubFilterParam", responseString))) {
                    binding.filterTxt.setText(generalFunc.getJsonValue("vSubFilterParam", responseString));
                    if (mActivity instanceof RideMyList activity) {
                        activity.vBookingParam = generalFunc.getJsonValue("vSubFilterParam", responseString);
                    } else if (mActivity instanceof RideSharingProHomeActivity activity) {
                        activity.rsRidesFragment.vBookingParam = generalFunc.getJsonValue("vSubFilterParam", responseString);
                    }
                }
                buildFilters(generalFunc.getJsonObject(responseString));
                mRideBookSearchAdapter.notifyDataSetChanged();
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
        mRideBookSearchAdapter.removeFooterView();
    }

    private void generateErrorView(String FilterType, boolean isScroll) {
        binding.rvRideBookingList.setVisibility(View.GONE);
        binding.filterArea.setVisibility(View.GONE);

        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setVisibility(View.VISIBLE);
        binding.errorView.setOnRetryListener(() -> GetBookingsRidesList(FilterType, isScroll));
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
            GetBookingsRidesList(vSubFilterParam, false);
            binding.rvRideBookingList.setVisibility(View.GONE);
            binding.noDataArea.setVisibility(View.GONE);

        }).show(populatePos(), "vTitle");
    }

    private int populatePos() {
        if (mActivity instanceof RideMyList activity) {
            return activity.vBookingPos;
        } else if (mActivity instanceof RideSharingProHomeActivity activity) {
            return activity.rsRidesFragment.vBookingPos;
        }
        return -1;
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
                    if (activity.vBookingParam.equalsIgnoreCase(vSubFilterParam)) {

                        activity.vBookingParam = vSubFilterParam;
                        activity.vBookingPos = i;

                        binding.filterTxt.setText(vTitle);
                    }
                } else if (mActivity instanceof RideSharingProHomeActivity activity) {
                    if (activity.rsRidesFragment.vBookingParam.equalsIgnoreCase(vSubFilterParam)) {

                        activity.rsRidesFragment.vBookingParam = vSubFilterParam;
                        activity.rsRidesFragment.vBookingPos = i;

                        binding.filterTxt.setText(vTitle);
                    }
                }
                subFilterList.add(map);
            }
        }
    }
}