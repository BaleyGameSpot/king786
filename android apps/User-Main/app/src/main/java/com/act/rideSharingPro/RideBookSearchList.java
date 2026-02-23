package com.act.rideSharingPro;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.rideSharingPro.adapter.RideBookSearchAdapter;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideBookSearchBinding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RideBookSearchList extends ParentActivity {

    private ActivityRideBookSearchBinding binding;
    private RideBookSearchAdapter mRideBookSearchAdapter;
    private final ArrayList<HashMap<String, String>> mRideBookSearchList = new ArrayList<>();
    private String mPage = "1", selectedNoOfSeats = "";
    boolean mIsLoading = false, isNextPageAvailable = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_book_search);

        selectedNoOfSeats = getIntent().getStringExtra("NoOfSeats");

        initialization();
        rideAlterView("");
        getBookSearchList(false);
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_RIDE_SEARCH_TITLE"));

        binding.loading.setVisibility(View.GONE);
        binding.noDataArea.setVisibility(View.GONE);
        binding.viewDeparturesArea.setVisibility(View.GONE);

        addToClickHandler(binding.rideAlterBtnTxt);
        addToClickHandler(binding.beforeDeparturesHTxt);
        addToClickHandler(binding.afterDeparturesHTxt);

        mRideBookSearchAdapter = new RideBookSearchAdapter(generalFunc, mRideBookSearchList, new RideBookSearchAdapter.OnClickListener() {
            @Override
            public void onItemClick(int position, HashMap<String, String> mapData) {
                Bundle bn = new Bundle();
                bn.putBoolean("isSearchView", true);
                mapData.put("selectedNoOfSeats", selectedNoOfSeats);
                bn.putSerializable("myRideDataHashMap", mapData);
                new ActUtils(getActivity()).startActForResult(RideBookDetails.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
            }

            @Override
            public void onTrackDriverBtnClick(int position, HashMap<String, String> mapData) {

            }

            @Override
            public void onSOSBtnClick(int position, HashMap<String, String> mapData) {

            }
        });
        mRideBookSearchAdapter.setIsFromSearchScreen(true);
        binding.rvRideBookSearchList.addItemDecoration(new SpacesItemDecoration(1, getResources().getDimensionPixelSize(R.dimen._12sdp), false));
        binding.rvRideBookSearchList.setAdapter(mRideBookSearchAdapter);

        //////////////////////
        binding.rvRideBookSearchList.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);

                int visibleItemCount = Objects.requireNonNull(recyclerView.getLayoutManager()).getChildCount();
                int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !mIsLoading && isNextPageAvailable) {

                    mIsLoading = true;
                    mRideBookSearchAdapter.addFooterView();
                    getBookSearchList(true);

                } else if (!isNextPageAvailable) {
                    mRideBookSearchAdapter.removeFooterView();
                }
            }
        });
    }

    private void rideAlterView(String responseString) {
        if (ServiceModule.EnableRideSharingPro && generalFunc.getJsonValue("IS_RIDE_ALERT_VIEW_OPEN", responseString).equalsIgnoreCase("Yes")) {

            binding.rideAlterArea.setVisibility(View.VISIBLE);
            binding.listingView.setVisibility(View.GONE);

            binding.beforeDeparturesHTxt.setText(generalFunc.getJsonValue("BEFORE_DEPARTURE_TEXT", responseString));
            if (Utils.checkText(Utils.getText(binding.beforeDeparturesHTxt))) {
                binding.beforeDeparturesArea.setVisibility(View.VISIBLE);
            } else {
                binding.beforeDeparturesArea.setVisibility(View.GONE);
            }
            binding.afterDeparturesHTxt.setText(generalFunc.getJsonValue("AFTER_DEPARTURE_TEXT", responseString));
            binding.rideAlterDateTxt.setText(generalFunc.getJsonValue("DAY_TEXT", responseString));
            binding.rideAlterMsgTxt.setText(generalFunc.getJsonValue("NO_RIDES_FOR_THIS_DAY_TEXT", responseString));
            binding.rideAlterBtnTxt.setText(generalFunc.getJsonValue("CREATE_RIDE_ALERT_TEXT", responseString));
        } else {
            binding.listingView.setVisibility(View.VISIBLE);
            binding.rideAlterArea.setVisibility(View.GONE);
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getBookSearchList(boolean isLoadMore) {
        if (!isLoadMore) {
            viewManage(true);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "SearchRide");

        parameters.put("tStartLat", getIntent().getStringExtra("tStartLat"));
        parameters.put("tStartLong", getIntent().getStringExtra("tStartLong"));

        parameters.put("tEndLat", getIntent().getStringExtra("tEndLat"));
        parameters.put("tEndLong", getIntent().getStringExtra("tEndLong"));

        parameters.put("dStartDate", getIntent().getStringExtra("dStartDate"));
        parameters.put("NoOfSeats", selectedNoOfSeats);

        parameters.put("vFilterParam", "");

        if (isLoadMore) {
            parameters.put("page", mPage);
        }

        String strDeparture = getIntent().getStringExtra("Departure");
        if (Utils.checkText(strDeparture)) {
            parameters.put("Departure", strDeparture);
            binding.viewDeparturesHTxt.setText(getIntent().getStringExtra("DepartureTitle"));
        }

        ApiHandler.execute(getActivity(), parameters, responseString -> {

            binding.loading.setVisibility(View.GONE);
            binding.noDataArea.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {

                rideAlterView(responseString);

                if (Utils.checkText(Utils.getText(binding.viewDeparturesHTxt))) {
                    binding.viewDeparturesArea.setVisibility(View.VISIBLE);
                }

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (!isLoadMore) {
                        mRideBookSearchList.clear();
                    }

                    String nextPage = generalFunc.getJsonValue("NextPage", responseString);

                    JSONArray dataArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    if (dataArray != null && dataArray.length() > 0) {
                        MyUtils.createArrayListJSONArray(generalFunc, mRideBookSearchList, dataArray);
                    } else {
                        noDataView(responseString);
                    }
                    mRideBookSearchAdapter.notifyDataSetChanged();

                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        mPage = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                } else {
                    noDataView(responseString);
                    removeNextPageConfig();
                }
            } else {
                generateErrorView(isLoadMore);
                if (!isLoadMore) {
                    removeNextPageConfig();
                }
            }
            mIsLoading = false;
        });
    }

    private void noDataView(String responseString) {
        binding.noDataArea.setVisibility(View.VISIBLE);
        binding.noDataTitleTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message_title", responseString)));
        binding.noDataMsgTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
    }

    private void removeNextPageConfig() {
        mPage = "";
        isNextPageAvailable = false;
        mIsLoading = false;
    }

    private void viewManage(boolean isVisible) {
        if (isVisible) {
            binding.errorView.setVisibility(View.GONE);
            binding.loading.setVisibility(View.VISIBLE);
            binding.rvRideBookSearchList.setVisibility(View.VISIBLE);
        } else {
            binding.errorView.setVisibility(View.VISIBLE);
            binding.loading.setVisibility(View.GONE);
            binding.rvRideBookSearchList.setVisibility(View.GONE);
        }
        binding.rideAlterArea.setVisibility(View.GONE);
        binding.noDataArea.setVisibility(View.GONE);
    }

    private void generateErrorView(boolean isLoadMore) {
        viewManage(false);
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setOnRetryListener(() -> getBookSearchList(isLoadMore));
    }

    private Context getActivity() {
        return RideBookSearchList.this;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == Activity.RESULT_OK) {
            if (data != null && data.getBooleanExtra("isShowRideBooking", false)) {
                new ActUtils(getActivity()).setOkResult(data.getExtras());
                finish();
            } else {
                getBookSearchList(false);
            }
        }
    }

    private void createAlter() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "addRideAlert");

        parameters.put("tStartLat", getIntent().getStringExtra("tStartLat"));
        parameters.put("tStartLong", getIntent().getStringExtra("tStartLong"));

        parameters.put("tEndLat", getIntent().getStringExtra("tEndLat"));
        parameters.put("tEndLong", getIntent().getStringExtra("tEndLong"));

        parameters.put("dStartDate", getIntent().getStringExtra("dStartDate"));
        parameters.put("NoOfSeats", selectedNoOfSeats);

        ApiHandler.execute(getActivity(), parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), i -> finish());
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.rideAlterBtnTxt.getId()) {
            createAlter();
        } else if (i == binding.beforeDeparturesHTxt.getId() || i == binding.afterDeparturesHTxt.getId()) {
            Bundle bn = getIntent().getExtras();
            if (i == binding.afterDeparturesHTxt.getId()) {
                bn.putString("Departure", "After");
                bn.putString("DepartureTitle", Utils.getText(binding.afterDeparturesHTxt));
            } else {
                bn.putString("Departure", "Before");
                bn.putString("DepartureTitle", Utils.getText(binding.beforeDeparturesHTxt));
            }
            new ActUtils(getActivity()).startActForResult(RideBookSearchList.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
        }
    }
}