package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.DriverFeedbackRecycleAdapter;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class DriverFeedbackActivity extends ParentActivity {

    MTextView titleTxt;
    MTextView vAvgRatingTxt;
    ImageView backImgView;

    ProgressBar loading_ride_history;
    MTextView noRidesTxt;

    RecyclerView historyRecyclerView;
    ErrorView errorView;

    DriverFeedbackRecycleAdapter feedbackRecyclerAdapter;

    ArrayList<HashMap<String, String>> list;

    boolean mIsLoading = false;
    boolean isNextPageAvailable = false;

    String next_page_str = "";
    String vAvgRating = "";
    LinearLayout avgRatingArea;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_driver_feedback);


        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        vAvgRatingTxt = (MTextView) findViewById(R.id.vAvgRatingTxt);

        loading_ride_history = (ProgressBar) findViewById(R.id.loading_ride_history);
        noRidesTxt = (MTextView) findViewById(R.id.noRidesTxt);
        historyRecyclerView = (RecyclerView) findViewById(R.id.historyRecyclerView);
        historyRecyclerView.setClipToPadding(false);
        avgRatingArea = (LinearLayout) findViewById(R.id.avgRatingArea);
        errorView = (ErrorView) findViewById(R.id.errorView);


        vAvgRating = generalFunc.getJsonValueStr("vAvgRating", obj_userProfile);
        vAvgRatingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AVERAGE_RATING_TXT") + " : " + vAvgRating);

        list = new ArrayList<>();
        feedbackRecyclerAdapter = new DriverFeedbackRecycleAdapter(getActContext(), list, generalFunc, false);
        historyRecyclerView.setAdapter(feedbackRecyclerAdapter);
        addToClickHandler(backImgView);
        setLabels();

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
                    getFeedback(true);
                }
            }
        });

        getFeedback(false);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    public void setLabels() {
        if (ServiceModule.isRideOnly()) {
            titleTxt.setText(generalFunc.retrieveLangLBl("Rider Feedback", "LBL_RIDER_FEEDBACK"));
        } else if (ServiceModule.isDeliveronly()) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SENDER_fEEDBACK"));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USER_FEEDBACK"));
        }
    }

    public void getFeedback(final boolean isLoadMore) {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        if (loading_ride_history.getVisibility() != View.VISIBLE && !isLoadMore) {
            loading_ride_history.setVisibility(View.VISIBLE);
        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "loadDriverFeedBack");
        parameters.put("iDriverId", generalFunc.getMemberId());

        Logger.d("next_page_str", ":" + next_page_str);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        noRidesTxt.setVisibility(View.GONE);

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {

                    noRidesTxt.setVisibility(View.GONE);

                    if (responseString != null && !responseString.equals("")) {

                        closeLoader();
                        if (generalFunc.checkDataAvail(Utils.action_str, responseString)) {

                            String nextPage = generalFunc.getJsonValue("NextPage", responseString);
                            vAvgRating = generalFunc.getJsonValue("vAvgRating", responseString);

                            vAvgRatingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AVERAGE_RATING_TXT") + " : " + vAvgRating);
                            JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseString);
                            if (arr_rides != null && arr_rides.length() > 0) {
                                for (int i = 0; i < arr_rides.length(); i++) {
                                    JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);
                                    HashMap<String, String> map = new HashMap<>();
                                    map.put("iRatingId", generalFunc.getJsonValueStr("iRatingId", obj_temp));
                                    map.put("iTripId", generalFunc.getJsonValueStr("iTripId", obj_temp));
                                    map.put("vRating1", generalFunc.getJsonValueStr("vRating1", obj_temp));
                                    String tDateOrig = generalFunc.getJsonValueStr("tDateOrig", obj_temp);
                                    map.put("tDateOrig", tDateOrig);
                                    /*map.put("tDateOrigConverted", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tDateOrig, Utils.OriginalDateFormate, DateTimeUtils.DateFormat))
                                    );*/
                                    map.put("vMessage", generalFunc.getJsonValueStr("vMessage", obj_temp));
                                    map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));
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

                            feedbackRecyclerAdapter.notifyDataSetChanged();
                            if (list.size() > 0)
                                avgRatingArea.setVisibility(View.VISIBLE);

                        } else {
                            if (list.size() == 0) {
                                removeNextPageConfig();
                                noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                                noRidesTxt.setVisibility(View.VISIBLE);
                                avgRatingArea.setVisibility(View.GONE);
                            }

                        }
                    } else {
                        if (!isLoadMore) {
                            removeNextPageConfig();
                            generateErrorView();
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

    public void closeLoader() {
        if (loading_ride_history.getVisibility() == View.VISIBLE) {
            loading_ride_history.setVisibility(View.GONE);
        }
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getFeedback(false));
    }

    public Context getActContext() {
        return DriverFeedbackActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(DriverFeedbackActivity.this);
        if (i == R.id.backImgView) {
            DriverFeedbackActivity.super.onBackPressed();
        }
    }

}
