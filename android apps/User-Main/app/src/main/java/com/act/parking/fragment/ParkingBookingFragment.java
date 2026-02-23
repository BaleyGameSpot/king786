package com.act.parking.fragment;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.dialogs.OpenListView;
import com.fragments.BaseFragment;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentParkingBookingBinding;
import com.act.parking.ParkingPublish;
import com.act.parking.ParkingPublishAndBooking;
import com.act.parking.ReviewOrCancelParkingBookingActivity;
import com.act.parking.adapter.MyParkingSpaceAdapter;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class ParkingBookingFragment extends BaseFragment {

    private FragmentParkingBookingBinding binding;
    private ParkingPublishAndBooking mActivity;
    private GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> listData = new ArrayList<>();
    int selectedPos = 0;
    private MyParkingSpaceAdapter mMyParkingSpaceAdapter;
    private final ArrayList<HashMap<String, String>> mySpacesList = new ArrayList<>();
    boolean mIsLoading = false, isNextPageAvailable = false;
    private String next_page_str = "1";

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_parking_booking, container, false);
        binding.loading.setVisibility(View.GONE);
        binding.noDataArea.setVisibility(View.GONE);
        addToClickHandler(binding.filterArea);
        addToClickHandler(binding.addParkingSpace);
        binding.filterArea.setVisibility(View.GONE);

        mMyParkingSpaceAdapter = new MyParkingSpaceAdapter(mActivity, generalFunc, mySpacesList, (position, mapData) -> {
            Bundle bn = new Bundle();
            bn.putString("isCancel", "yes");
            bn.putSerializable("responseMap", mapData);
            bn.putInt("position", position);
            new ActUtils(mActivity).startActWithData(ReviewOrCancelParkingBookingActivity.class, bn);

        });
        binding.rvParkingPublishList.addItemDecoration(new SpacesItemDecoration(1, getResources().getDimensionPixelSize(R.dimen._12sdp), false));
        binding.rvParkingPublishList.setAdapter(mMyParkingSpaceAdapter);
        binding.rvParkingPublishList.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (recyclerView.canScrollVertically(1)) {
                    int visibleItemCount = Objects.requireNonNull(binding.rvParkingPublishList.getLayoutManager()).getChildCount();
                    int totalItemCount = binding.rvParkingPublishList.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) binding.rvParkingPublishList.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if (((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable)) {
                        mIsLoading = true;
                        binding.footerLoader.setVisibility(View.VISIBLE);
                        binding.rvParkingPublishList.stopScroll();
                        GetParkingBookingsList(true);

                    } else if (!isNextPageAvailable) {
                        binding.footerLoader.setVisibility(View.GONE);
                    }
                }
            }
        });

        GetParkingBookingsList(false);

        return binding.getRoot();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof ParkingPublishAndBooking) {
            mActivity = (ParkingPublishAndBooking) requireActivity();
            generalFunc = mActivity.generalFunc;
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void GetParkingBookingsList(boolean isScroll) {
        if (!isScroll) {
            listData.clear();
            binding.loading.setVisibility(View.VISIBLE);

        } else {
            binding.loading.setVisibility(View.GONE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "FetchParkingSpaceBookings");
        parameters.put("GeneralMemberId", generalFunc.getMemberId());
        parameters.put("page", next_page_str);


        ApiHandler.execute(mActivity, parameters, responseString -> {
            mIsLoading = false;
            String nextPage = generalFunc.getJsonValue("NextPage", responseString);
            binding.loading.setVisibility(View.GONE);
            binding.noDataArea.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    JSONArray dataArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    JSONArray filterArray = generalFunc.getJsonArray("FilterOption", responseString);
                    if (filterArray.length() > 0) {
                        binding.filterArea.setVisibility(View.VISIBLE);
                    } else {
                        binding.filterArea.setVisibility(View.GONE);
                    }
                    MyUtils.createArrayListJSONArray(generalFunc, mySpacesList, dataArray);
                    MyUtils.createArrayListJSONArray(generalFunc, listData, filterArray);
                    for (int i = 0; i < dataArray.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(filterArray, i);
                    }


                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
                if (mySpacesList.size() == 0) {
                    binding.noDataArea.setVisibility(View.VISIBLE);
                    binding.noDataTitleTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)));
                    binding.noDataMsgTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
                mMyParkingSpaceAdapter.notifyDataSetChanged();
            } else {
                removeNextPageConfig();
                generalFunc.showError();
            }
        });
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == binding.filterArea.getId()) {

            OpenListView.getInstance(mActivity, generalFunc.retrieveLangLBl("Select Type", "LBL_SELECT_TYPE"), listData, OpenListView.OpenDirection.BOTTOM, true, position -> {
                selectedPos = position;
                binding.filterTxt.setText(listData.get(position).get("vTitle"));
                mySpacesList.clear();
                GetParkingBookingsList(false);


            }).show(selectedPos, "vTitle");

        } else if (i == binding.addParkingSpace.getId()) {
            new ActUtils(mActivity).startAct(ParkingPublish.class);
        }

    }


    private void removeNextPageConfig() {
        next_page_str = "1";
        isNextPageAvailable = false;
        mIsLoading = false;
        binding.footerLoader.setVisibility(View.GONE);
    }


}