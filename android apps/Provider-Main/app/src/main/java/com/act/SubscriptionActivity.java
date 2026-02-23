package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.SubscriptionAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivitySubscriptionBinding;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class SubscriptionActivity extends ParentActivity implements SubscriptionAdapter.OnItemClickListener {

    private ActivitySubscriptionBinding binding;
    private final int TRANSACTION_COMPLETED = 12345;

    private ImageView rightImgView;
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
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_PLANS"));

        rightImgView = findViewById(R.id.rightImgView);
        rightImgView.setImageResource(R.drawable.ic_waybill);
        rightImgView.setColorFilter(getActContext().getResources().getColor(R.color.white));
        rightImgView.setVisibility(View.VISIBLE);
        addToClickHandler(rightImgView);

        //
        binding.memberShipTitleTxt.setVisibility(View.VISIBLE);
        binding.memberShipTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_MEMBERSHIP"));

        binding.subscriptionTypeTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIBE_WITH_US"));
        binding.subscriptionDesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_GUIDE_TXT"));
        binding.noPlansTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_GUIDE_TXT"));
    }

    private void listView() {
        subscriptionAdapter = new SubscriptionAdapter(list, "");
        binding.subscriptionRecyclerView.setAdapter(subscriptionAdapter);
        subscriptionAdapter.setOnItemClickListener(this);

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
                    getSubscriptionPlans(true);
                    subscriptionAdapter.addFooterView();

                } else if (!isNextPageAvailable) {
                    subscriptionAdapter.removeFooterView();
                }
            }
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        getSubscriptionPlans(false);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == TRANSACTION_COMPLETED && resultCode == RESULT_OK) {
            getSubscriptionPlans(false);
        }
    }

    private Context getActContext() {
        return SubscriptionActivity.this; // Must be context of activity not application
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getSubscriptionPlans(boolean isLoadMore) {
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
        parameters.put("type", "getSubscriptionPlans");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (responseStringObj != null) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObj);
                    binding.contentArea.setVisibility(View.VISIBLE);

                    JSONArray arr_subsucription_plans = generalFunc.getJsonArray(Utils.message_str, responseStringObj);

                    if (arr_subsucription_plans != null && arr_subsucription_plans.length() > 0) {

                        list.clear();

                        for (int i = 0; i < arr_subsucription_plans.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_subsucription_plans, i);

                            HashMap<String, String> map = new HashMap<String, String>();
                            map.put("iDriverSubscriptionPlanId", generalFunc.getJsonValueStr("iDriverSubscriptionPlanId", obj_temp));
                            map.put("iDriverSubscriptionDetailsId", generalFunc.getJsonValueStr("iDriverSubscriptionDetailsId", obj_temp));
                            map.put("PlanTypeTitle", generalFunc.getJsonValueStr("PlanTypeTitle", obj_temp));

                            String planType = generalFunc.getJsonValueStr("PlanType", obj_temp);
                            map.put("PlanType", planType);
                            map.put("vPlanDuration", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vPlanDuration", obj_temp)));

                            map.put("vPlanName", generalFunc.getJsonValueStr("vPlanName", obj_temp));
                            map.put("vPlanPeriod", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vPlanPeriod", obj_temp)));
                            map.put("vPlanDescription", generalFunc.getJsonValueStr("vPlanDescription", obj_temp));
                            map.put("isRenew", generalFunc.getJsonValueStr("isRenew", obj_temp));
                            String planLeftDays = generalFunc.getJsonValueStr("planLeftDays", obj_temp);

                            map.put("planLeftDays", planLeftDays);
                            map.put("FormattedPlanLeftDays", generalFunc.convertNumberWithRTL(planLeftDays));

                            map.put("PlanDuration", generalFunc.retrieveLangLBl("", "LBL_SUB_DURATION_TXT") + " " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("PlanDuration", obj_temp)));

                            map.put("fPlanPrice", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fPlanPrice", obj_temp)));
                            String eSubscriptionStatus = generalFunc.getJsonValueStr("eSubscriptionStatus", obj_temp);
                            map.put("eSubscriptionStatus", eSubscriptionStatus);

                            String listLbl = "LBL_SUBSCRIBE";
                            String detailLbl = "";
                            String showPlanDetails = "No";

                            if (eSubscriptionStatus.equalsIgnoreCase("Subscribed")) {
                                listLbl = "LBL_CANCEL_SUBSCRIPTION_TXT";
                                detailLbl = "LBL_SUBSCRIBED_TXT";
                                showPlanDetails = "Yes";

                            } else if (eSubscriptionStatus.equalsIgnoreCase("UnSubscribed")) {
                                detailLbl = "LBL_NOT_SUBSCRIBED_TXT";

                            } else if (eSubscriptionStatus.equalsIgnoreCase("Expired")) {
                                detailLbl = "LBL_SUB_EXPIRED_TXT";
                            } else if (eSubscriptionStatus.equalsIgnoreCase("Cancelled")) {
                                detailLbl = "LBL_SUB_CANCELLED_TXT";
                            }
                            map.put("eSubscriptionStatusLbl", generalFunc.retrieveLangLBl("", listLbl));
                            Log.d("eSubscriptionStatus", "listLbl" + listLbl);
                            Log.d("eSubscriptionStatus", "detailLbl" + detailLbl);

                            map.put("eSubscriptionDetailStatusLbl", generalFunc.retrieveLangLBl("", detailLbl));
                            map.put("showPlanDetails", showPlanDetails);

                            String tSubscribeDate = generalFunc.getJsonValueStr("tSubscribeDate", obj_temp);
                            String tExpiryDate = generalFunc.getJsonValueStr("tExpiryDate", obj_temp);
                            //map.put("tSubscribeDate", tSubscribeDate.equalsIgnoreCase("N/A") ? tSubscribeDate : generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tSubscribeDate, Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                            //map.put("tExpiryDate", tExpiryDate.equalsIgnoreCase("N/A") ? tExpiryDate : generalFunc.convertNumberWithRTL(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tExpiryDate, Utils.OriginalDateFormate, DateTimeUtils.DateFormat))));
                            map.put("tSubscribeDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                            map.put("tExpiryDate", generalFunc.getJsonValueStr("tExpiryDisplayDate", obj_temp));

                            map.put("subscribedOnLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_ON_TXT"));
                            map.put("expiredOnLBL", generalFunc.retrieveLangLBl("", "LBL_SUB_EXPIRED_ON_TXT"));

                            map.put("statusLbl", generalFunc.retrieveLangLBl("", "LBL_Status"));
                            map.put("vPlanDescriptionLbl", generalFunc.retrieveLangLBl("", "LBL_DETAILS"));
                            map.put("subscriptionLbl", generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_TXT"));
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

                } else {
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
        binding.errorView.setOnRetryListener(() -> getSubscriptionPlans(false));
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            SubscriptionActivity.super.onBackPressed();

        } else if (i == R.id.rightImgView) {
            MyUtils.setBounceAnimation(getActContext(), rightImgView, R.anim.bounce_interpolator, () -> new ActUtils(getActContext()).startAct(SubscriptionHistoryActivity.class));
        }
    }

    @Override
    public void onSubscribeItemClick(View v, int position, String planType) {

        HashMap<String, String> item = list.get(position);
        if (item.get("eSubscriptionStatus").equalsIgnoreCase("Subscribed")) {
            if (planType.equalsIgnoreCase("Cancel")) {
                buildMsgOnCancelSubscription(position);
            } else if (planType.equalsIgnoreCase("Renew")) {
                Bundle bn = new Bundle();
                bn.putSerializable("PlanDetails", item);
                bn.putSerializable("isRenew", "Yes");
                new ActUtils(getActContext()).startActForResult(SubscriptionPaymentActivity.class, bn, TRANSACTION_COMPLETED);
            }
        } else if (!item.get("eSubscriptionStatus").equalsIgnoreCase("Subscribed")) {
            Bundle bn = new Bundle();
            bn.putSerializable("PlanDetails", item);
            new ActUtils(getActContext()).startActForResult(SubscriptionPaymentActivity.class, bn, TRANSACTION_COMPLETED);
        }
    }

    private void buildMsgOnCancelSubscription(int pos) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else {
                cancelSubscription(pos);
                generateAlert.closeAlertBox();
            }
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CANCEL_SUBSCRIPTION_NOTE_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
        generateAlert.showAlertBox();
    }

    private void cancelSubscription(int pos) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CancelSubscription");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("iDriverSubscriptionPlanId", list.get(pos).get("iDriverSubscriptionPlanId"));
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    getSubscriptionPlans(false);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                }

            } else {
                generateErrorView();
            }
            closeLoader();
            mIsLoading = false;
        });
    }
}
