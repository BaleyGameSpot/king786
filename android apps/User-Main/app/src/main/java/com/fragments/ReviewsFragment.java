package com.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.adapter.files.DriverFeedbackRecycleAdapter;
import com.act.MoreInfoActivity;
import com.buddyverse.main.R;
import com.general.files.GeneralFunctions;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class ReviewsFragment extends BaseFragment {
    View view;
    MTextView avgRatingTxt;
    SimpleRatingBar avgRatingBar;
    MoreInfoActivity moreInfoActivity;
    RecyclerView historyRecyclerView;
    DriverFeedbackRecycleAdapter feedbackRecyclerAdapter;
    ProgressBar loading_ride_history;
    MTextView noRidesTxt;
    String next_page_str = "";
    GeneralFunctions generalFunc;
    String vAvgRating;
    ArrayList<HashMap<String, String>> list;
    boolean isNextPageAvailable = false;
    boolean mIsLoading = false;
    LinearLayout avgRatingArea;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        view = inflater.inflate(R.layout.fragment_reviews, container, false);

        moreInfoActivity = (MoreInfoActivity) getActivity();
        generalFunc = moreInfoActivity.generalFunc;
        avgRatingTxt = (MTextView) view.findViewById(R.id.avgRatingTxt);
        avgRatingBar = (SimpleRatingBar) view.findViewById(R.id.avgRatingBar);
        loading_ride_history = (ProgressBar) view.findViewById(R.id.loading_ride_history);
        noRidesTxt = (MTextView) view.findViewById(R.id.noRidesTxt);
        historyRecyclerView = (RecyclerView) view.findViewById(R.id.historyRecyclerView);
        avgRatingArea = (LinearLayout) view.findViewById(R.id.avgRatingArea);
        list = new ArrayList<>();
        feedbackRecyclerAdapter = new DriverFeedbackRecycleAdapter(moreInfoActivity.getActContext(), list, generalFunc, false);
        historyRecyclerView.setAdapter(feedbackRecyclerAdapter);
        historyRecyclerView.setClipToPadding(false);

        historyRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);

                int visibleItemCount = recyclerView.getLayoutManager().getChildCount();
                int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {

                    mIsLoading = true;
                    feedbackRecyclerAdapter.addFooterView();
                    historyRecyclerView.stopScroll();
                    recyclerView.post(() -> feedbackRecyclerAdapter.notifyDataSetChanged());
                    getFeedback(true);

                } else if (!isNextPageAvailable) {
                    feedbackRecyclerAdapter.removeFooterView();
                }
            }
        });
        getFeedback(false);
        return view;
    }

    public void closeLoader() {
        if (loading_ride_history.getVisibility() == View.VISIBLE) {
            loading_ride_history.setVisibility(View.GONE);
        }
    }

    public void getFeedback(final boolean isLoadMore) {

        if (loading_ride_history.getVisibility() != View.VISIBLE && !isLoadMore) {
            loading_ride_history.setVisibility(View.VISIBLE);
        }

        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "loadDriverFeedBack");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iDriverId", moreInfoActivity.getIntent().getStringExtra("iDriverId"));
        parameters.put("SelectedCabType", Utils.CabGeneralType_UberX);

        Logger.d("next_page_str", ":" + next_page_str);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        noRidesTxt.setVisibility(View.GONE);

        ApiHandler.execute(moreInfoActivity.getActContext(), parameters, (ServerTask.SetDataResponse) responseString -> {
            noRidesTxt.setVisibility(View.GONE);
            closeLoader();
            if (responseString != null && !responseString.equals("")) {
                if (generalFunc.checkDataAvail(Utils.action_str, responseString)) {

                    String nextPage = generalFunc.getJsonValue("NextPage", responseString);
                    vAvgRating = generalFunc.getJsonValue("vAvgRating", responseString);

                    avgRatingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AVERAGE_RATING_TXT"));
                    avgRatingBar.setRating(GeneralFunctions.parseFloatValue(0, vAvgRating));

                    JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseString);

                    if (arr_rides != null && arr_rides.length() > 0) {
                        for (int i = 0; i < arr_rides.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);

                            HashMap<String, String> map = new HashMap<String, String>();

                            map.put("iRatingId", generalFunc.getJsonValueStr("iRatingId", obj_temp));
                            map.put("iTripId", generalFunc.getJsonValueStr("iTripId", obj_temp));
                            map.put("vRating1", generalFunc.getJsonValueStr("vRating1", obj_temp));
                            map.put("tDateOrig", generalFunc.getJsonValueStr("tDateOrig", obj_temp));
                            map.put("vMessage", generalFunc.getJsonValueStr("vMessage", obj_temp));
                            map.put("vName", generalFunc.getJsonValueStr("vName", obj_temp));
                            map.put("vImage", generalFunc.getJsonValueStr("vImage", obj_temp));


                            map.put("LBL_READ_MORE", generalFunc.retrieveLangLBl("", "LBL_READ_MORE"));
                            map.put("JSON", obj_temp.toString());

                            list.add(map);

                        }
                    }

                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }

                    if (list.size() == 0) {
                        removeNextPageConfig();
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        noRidesTxt.setVisibility(View.VISIBLE);
                    }
                } else {
                    if (list.size() == 0) {
                        removeNextPageConfig();
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        noRidesTxt.setVisibility(View.VISIBLE);
                    }
                }
                feedbackRecyclerAdapter.notifyDataSetChanged();
            } else {
                if (!isLoadMore) {
                    removeNextPageConfig();
                }
            }
            mIsLoading = false;
        });

    }

    public void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        feedbackRecyclerAdapter.removeFooterView();
    }

}