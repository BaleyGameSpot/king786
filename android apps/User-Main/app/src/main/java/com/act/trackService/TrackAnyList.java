package com.act.trackService;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.act.UberXHomeActivity;
import com.act.trackService.adapter.TrackAnyAdapter;
import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityTrackAnyListBinding;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class TrackAnyList extends ParentActivity implements SwipeRefreshLayout.OnRefreshListener {

    private ActivityTrackAnyListBinding binding;
    private ImageView backImgView, ic_iv_add;

    private TrackAnyAdapter mTrackAnyAdapter;
    private final ArrayList<HashMap<String, String>> trackAnyList = new ArrayList<>();

    boolean mIsLoading = false, isNextPageAvailable = false;
    private String next_page_str = "1";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_track_any_list);

        toolBarView();

        binding.noDataTrackAnyTxt.setVisibility(View.GONE);
        binding.noDataTrackAnyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));

        mTrackAnyAdapter = new TrackAnyAdapter(generalFunc, trackAnyList, new TrackAnyAdapter.ItemClickListener() {
            @Override
            public void onProfileClick(int position, HashMap<String, String> itemList) {
                Bundle bundle = new Bundle();
                bundle.putSerializable("trackAnyHashMap", itemList);
                new ActUtils(TrackAnyList.this).startActWithData(TrackAnyProfileVehicle.class, bundle);
            }

            @Override
            public void onVehicleClick(int position, HashMap<String, String> itemList) {
                Bundle bundle = new Bundle();
                bundle.putBoolean("isVehicleView", true);
                bundle.putSerializable("trackAnyHashMap", itemList);
                new ActUtils(TrackAnyList.this).startActWithData(TrackAnyProfileVehicle.class, bundle);
            }

            @Override
            public void ondelUserclick(int position, HashMap<String, String> itemList) {
                dltUser(itemList.get("iTrackServiceUserId"));
            }

            @Override
            public void onLiveTrackClick(int position, HashMap<String, String> itemList) {
                Bundle bundle = new Bundle();
                bundle.putSerializable("trackAnyHashMap", itemList);
                new ActUtils(TrackAnyList.this).startActForResult(TrackAnyLiveTracking.class, bundle, MyUtils.REFRESH_DATA_REQ_CODE);
            }
        });
        binding.rvTrackAny.setAdapter(mTrackAnyAdapter);
        binding.swipeRefreshTrackAny.setOnRefreshListener(this);
        binding.rvTrackAny.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (recyclerView.canScrollVertically(1)) {
                    int visibleItemCount = Objects.requireNonNull(binding.rvTrackAny.getLayoutManager()).getChildCount();
                    int totalItemCount = binding.rvTrackAny.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) binding.rvTrackAny.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if (((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable)) {
                        mIsLoading = true;
                        binding.footerLoader.setVisibility(View.VISIBLE);
                        binding.rvTrackAny.stopScroll();
                        getTrackList(false);

                    } else if (!isNextPageAvailable) {
                        binding.footerLoader.setVisibility(View.GONE);
                    }
                }
            }
        });
        getTrackList(true);
    }


    private void dltUser(String PairedUserId) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(this);
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else {
                dltMember(PairedUserId);
            }

        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("Logout", "LBL_DELETE_CONFIRM_MSG"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
        generateAlert.showAlertBox();
    }

    private void dltMember(String iTrackServiceUserId) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "removeLinkedMember");
        parameters.put("PairedUserId", iTrackServiceUserId);

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    getTrackList(true);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void toolBarView() {
        backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_ANY_TXT"));
        ic_iv_add = findViewById(R.id.ic_iv_add);
        ic_iv_add.setVisibility(View.VISIBLE);
        addToClickHandler(backImgView);
        addToClickHandler(ic_iv_add);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getTrackList(boolean isLoader) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "listTrackingUsers");
        parameters.put("page", next_page_str);

        if (getIntent().getStringExtra("MemberType") != null) {
            parameters.put("MemberType", getIntent().getStringExtra("MemberType"));
        }

        binding.loadingTrackAny.setVisibility(isLoader ? View.VISIBLE : View.GONE);
        binding.icIvAdd.setVisibility(View.GONE);
        binding.rvTrackAny.setVisibility(View.GONE);

        ApiHandler.execute(this, parameters, responseString -> {
            mIsLoading = false;
            String nextPage = generalFunc.getJsonValue("NextPage", responseString);
            binding.swipeRefreshTrackAny.setRefreshing(false);
            binding.loadingTrackAny.setVisibility(View.GONE);
            binding.noDataTrackAnyTxt.setVisibility(View.GONE);
            binding.noDataTrackAnyTxt.setVisibility(View.GONE);
            binding.icIvAdd.setVisibility(View.VISIBLE);
            binding.rvTrackAny.setVisibility(View.VISIBLE);

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    trackAnyList.clear();

                    JSONArray userArr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    if (userArr != null && userArr.length() > 0) {
                        MyUtils.createArrayListJSONArray(generalFunc, trackAnyList, userArr);
                        if (!nextPage.equals("") && !nextPage.equals("0")) {
                            next_page_str = nextPage;
                            isNextPageAvailable = true;
                        } else {
                            removeNextPageConfig();
                        }
                    } else {
                        binding.noDataTrackAnyTxt.setVisibility(View.VISIBLE);
                    }
                    mTrackAnyAdapter.notifyDataSetChanged();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }

            } else {
                removeNextPageConfig();
                generalFunc.showError();
            }
        });
    }

    private void removeNextPageConfig() {
        next_page_str = "1";
        isNextPageAvailable = false;
        mIsLoading = false;
        binding.footerLoader.setVisibility(View.GONE);
    }

    @Override
    public void onBackPressed() {
        if (getIntent().getBooleanExtra("isRestartApp", false)) {
            Bundle bn = new Bundle();
            new ActUtils(this).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public void onRefresh() {
        binding.swipeRefreshTrackAny.setRefreshing(true);
        getTrackList(false);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == Activity.RESULT_OK) {
            getTrackList(true);
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == backImgView.getId()) {
            onBackPressed();
        } else if (i == ic_iv_add.getId()) {
            Bundle bn = new Bundle();
            bn.putString("MemberType", getIntent().getStringExtra("MemberType"));
            new ActUtils(this).startActWithData(PairCodeGenrateActivity.class, bn);
        }
    }

    public void pubNubMsgArrived(final String message) {

        runOnUiThread(() -> {
            String msgType = generalFunc.getJsonValue("MsgType", message);
            if (msgType.equalsIgnoreCase("TrackingStatus")) {
                getTrackList(true);
            } else if (msgType.equals("TrackMemberRemoved")) {
                generalFunc.showGeneralMessage("", generalFunc.getJsonValue
                        ("vTitle", message), buttonId -> {
                    getTrackList(true);
                });
            }
        });
    }
}