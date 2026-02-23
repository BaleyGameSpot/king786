package com.act;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.SubscriptionAdapter;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivitySubscriptionBinding;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class SubscriptionHistoryActivity extends ParentActivity {

    private ActivitySubscriptionBinding binding;
    private SubscriptionAdapter subscriptionAdapter;
    private final ArrayList<HashMap<String, String>> list = new ArrayList<>();

    private String next_page_str = "";
    private boolean mIsLoading = false, isNextPageAvailable = false;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_subscription);

        initialization();
        listView();
        getDriverSubscriptionHistory(false);
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_HISTORY_TITLE_TXT"));

        binding.ivIcon.setImageResource(R.drawable.ic_waybill);

        binding.subscriptionTypeTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_HISTORY_TXT"));
        binding.subscriptionDesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_HISTORY_DESC_TXT"));
    }

    private void listView() {
        subscriptionAdapter = new SubscriptionAdapter(list, "History");
        binding.subscriptionRecyclerView.setAdapter(subscriptionAdapter);

        binding.subscriptionRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);

                int visibleItemCount = Objects.requireNonNull(recyclerView.getLayoutManager()).getChildCount();
                int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {

                    mIsLoading = true;
                    getDriverSubscriptionHistory(true);
                    subscriptionAdapter.addFooterView();

                } else if (!isNextPageAvailable) {
                    subscriptionAdapter.removeFooterView();
                }
            }
        });
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getDriverSubscriptionHistory(boolean isLoadMore) {
        if (binding.errorView.getVisibility() == View.VISIBLE) {
            binding.errorView.setVisibility(View.GONE);
        }
        if (binding.contentArea.getVisibility() == View.VISIBLE) {
            binding.contentArea.setVisibility(View.GONE);
        }
        if (binding.loading.getVisibility() != View.VISIBLE) {
            binding.loading.setVisibility(View.VISIBLE);
        }


        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDriverSubscriptionHistory");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        ApiHandler.execute(this, parameters, responseString -> {

            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (responseStringObj != null) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObj);
                    binding.contentArea.setVisibility(View.VISIBLE);

                    JSONArray arr_subsucription_plans = generalFunc.getJsonArray(Utils.message_str, responseStringObj);

                    if (arr_subsucription_plans != null && arr_subsucription_plans.length() > 0) {

                        for (int i = 0; i < arr_subsucription_plans.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_subsucription_plans, i);


                            HashMap<String, String> map = new HashMap<>();
                            map.put("iDriverSubscriptionPlanId", generalFunc.getJsonValueStr("iDriverSubscriptionPlanId", obj_temp));
                            map.put("iDriverSubscriptionDetailsId", generalFunc.getJsonValueStr("iDriverSubscriptionDetailsId", obj_temp));
                            map.put("PlanTypeTitle", generalFunc.getJsonValueStr("PlanTypeTitle", obj_temp));
                            map.put("PlanType", generalFunc.getJsonValueStr("PlanType", obj_temp));

                            map.put("vPlanName", generalFunc.getJsonValueStr("vPlanName", obj_temp));
                            map.put("PlanDuration", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("PlanDuration", obj_temp)));
                            map.put("vPlanPeriod", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vPlanPeriod", obj_temp)));
                            map.put("vPlanDescription", generalFunc.getJsonValueStr("vPlanDescription", obj_temp));
                            String planLeftDays = generalFunc.getJsonValueStr("planLeftDays", obj_temp);
                            map.put("planLeftDays", planLeftDays);
                            map.put("FormattedPlanLeftDays", generalFunc.convertNumberWithRTL(planLeftDays));

                            map.put("fPlanPrice", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fPlanPrice", obj_temp)));
                            String eSubscriptionStatus = generalFunc.getJsonValueStr("eSubscriptionStatus", obj_temp);
                            map.put("eSubscriptionStatus", eSubscriptionStatus);


                            String tSubscribeDate = generalFunc.getJsonValueStr("tSubscribeDate", obj_temp);
                            String tExpiryDate = generalFunc.getJsonValueStr("tExpiryDate", obj_temp);

                            //map.put("tSubscribeDate", tSubscribeDate.equalsIgnoreCase("N/A") ? tSubscribeDate : generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tSubscribeDate, Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                            //map.put("tExpiryDate", tExpiryDate.equalsIgnoreCase("N/A") ? tExpiryDate : generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tExpiryDate, Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                            map.put("tSubscribeDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                            map.put("tExpiryDate", generalFunc.getJsonValueStr("tExpiryDisplayDate", obj_temp));
                            map.put("isRenew", generalFunc.getJsonValueStr("isRenew", obj_temp));


                            String lbl = "";
                            if (eSubscriptionStatus.equalsIgnoreCase("Subscribed")) {
                                lbl = "LBL_SUB_ACTIVE_TXT";
                            } else if (eSubscriptionStatus.equalsIgnoreCase("Expired")) {
                                lbl = "LBL_SUB_EXPIRED_TXT";
                            } else if (eSubscriptionStatus.equalsIgnoreCase("Inactive")) {
                                lbl = "LBL_SUB_INACTIVE_TXT";
                            } else if (eSubscriptionStatus.equalsIgnoreCase("Cancelled")) {
                                lbl = "LBL_SUB_CANCELLED_TXT";
                            }


                            map.put("eSubscriptionStatusLbl", generalFunc.retrieveLangLBl("", lbl));
                            map.put("statusLbl", generalFunc.retrieveLangLBl("", "LBL_Status"));
                            map.put("vPlanDescriptionLbl", generalFunc.retrieveLangLBl("", "LBL_DETAILS"));
                            map.put("subscriptionLbl", generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_TXT"));
                            map.put("planLeftDaysTitle1", generalFunc.retrieveLangLBl("", "LBL_DAYS"));
                            map.put("planLeftDaysTitle2", generalFunc.retrieveLangLBl("", "LBL_SUB_DAYS_LEFT_TXT"));
                            map.put("subscribedStatusLbl", generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_STATUS_TXT"));
//                                    map.put("planTypeLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_PLAN_TYPE_TXT"));
                            map.put("planTypeLBL", generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_PLAN_NAME"));
                            map.put("planDurationLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_PLAN_DURATION_TXT"));
                            map.put("planPriceLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_PLAN_PRICE_TXT"));
                            map.put("subscribedOnLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_ON_TXT"));
                            map.put("expiredOnLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_EXPIRED_ON_TXT"));
                            map.put("renewLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_RENEW_PLAN_TXT"));

                            list.add(map);
                        }

                        if (!nextPage.equals("") && !nextPage.equals("0")) {
                            next_page_str = nextPage;
                            isNextPageAvailable = true;
                        } else {
                            removeNextPageConfig();
                        }

                        subscriptionAdapter.notifyDataSetChanged();
                        binding.scrollView.setScrollY(0);

                    } else {
                        list.clear();
                        if (list.size() == 0) {
                            removeNextPageConfig();
                            binding.noPlansTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                            binding.noPlansTxt.setVisibility(View.VISIBLE);
                            subscriptionAdapter.notifyDataSetChanged();
                        }
                    }

                } else if (!isLoadMore) {
                    list.clear();
                    if (list.size() == 0) {
                        removeNextPageConfig();
                        binding.noPlansTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                        binding.noPlansTxt.setVisibility(View.VISIBLE);
                        subscriptionAdapter.notifyDataSetChanged();
                    }
                }
            } else {
                generateErrorView();
            }
            closeLoader();
            mIsLoading = false;
        });
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        subscriptionAdapter.removeFooterView();
    }

    private void closeLoader() {
        if (binding.loading.getVisibility() == View.VISIBLE) {
            binding.loading.setVisibility(View.GONE);
        }
    }

    private void generateErrorView() {

        closeLoader();
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (binding.errorView.getVisibility() != View.VISIBLE) {
            binding.errorView.setVisibility(View.VISIBLE);
        }
        binding.errorView.setOnRetryListener(() -> getDriverSubscriptionHistory(false));
    }

    public void onClick(View view) {
        Utils.hideKeyboard(SubscriptionHistoryActivity.this);
        int i = view.getId();
        if (i == R.id.backImgView) {
            SubscriptionHistoryActivity.super.onBackPressed();
        }
    }
}
