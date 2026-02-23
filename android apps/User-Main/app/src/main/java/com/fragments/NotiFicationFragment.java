package com.fragments;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.adapter.files.NotificationAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.act.NotificationActivity;
import com.act.NotificationDetailsActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentNotificationBinding;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class NotiFicationFragment extends Fragment {

    private FragmentNotificationBinding binding;
    private NotificationActivity mActivity;
    private GeneralFunctions generalFunc;

    private NotificationAdapter notificationAdapter;
    private final ArrayList<HashMap<String, String>> list = new ArrayList<>();
    private String type = "", next_page_str = "";
    boolean mIsLoading = false, isNextPageAvailable = false;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof NotificationActivity activity) {
            mActivity = activity;
            generalFunc = activity.generalFunc;

            notificationAdapter = new NotificationAdapter(mActivity, generalFunc, list, map -> {
                Bundle bn = new Bundle();
                bn.putSerializable("data", map);
                new ActUtils(mActivity).startActWithData(NotificationDetailsActivity.class, bn);
            });

        }
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_notification, container, false);

        type = getArguments() != null ? getArguments().getString("type") : "";

        binding.notificationRV.setAdapter(notificationAdapter);
        binding.notificationRV.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);

                int visibleItemCount = Objects.requireNonNull(recyclerView.getLayoutManager()).getChildCount();
                int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {

                    mIsLoading = true;
                    notificationAdapter.addFooterView();
                    recyclerView.post(() -> notificationAdapter.notifyItemInserted(list.size() + 1));
                    binding.notificationRV.stopScroll();
                    getNotificationDetails(true);

                } else if (!isNextPageAvailable) {
                    notificationAdapter.removeFooterView();
                }
            }
        });

        //
        list.clear();
        getNotificationDetails(false);
        return binding.getRoot();
    }

    private void getNotificationDetails(boolean isLoadMore) {

        binding.errorView.setVisibility(View.GONE);
        if (!isLoadMore) {
            binding.mProgressBar.setVisibility(View.VISIBLE);
        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getNewsNotification");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("eType", type);

        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        ApiHandler.execute(mActivity, parameters, responseString -> {

            mIsLoading = false;
            binding.mProgressBar.setVisibility(View.GONE);
            binding.noDataTxt.setVisibility(View.GONE);
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (responseStringObj != null) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObj);

                    JSONArray arr_notifications = generalFunc.getJsonArray(Utils.message_str, responseStringObj);
                    if (arr_notifications != null && arr_notifications.length() > 0) {
                        if (!isLoadMore) {
                            list.clear();
                        }

                        for (int i = 0; i < arr_notifications.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_notifications, i);
                            HashMap<String, String> map = new HashMap<>();
                            map.put("iNewsfeedId", generalFunc.getJsonValueStr("iNewsfeedId", obj_temp));
                            map.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
                            map.put("vImage", generalFunc.getJsonValueStr("vImage", obj_temp));
                            map.put("tDescription", generalFunc.getJsonValueStr("tDescription", obj_temp));
                            //map.put("dDateTime", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValueStr("dDateTime", obj_temp), Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                            map.put("eStatus", generalFunc.getJsonValueStr("eStatus", obj_temp));
                            map.put("eType", generalFunc.getJsonValueStr("eType", obj_temp));
                            map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                            map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_temp));
                            map.put("tDisplayTimeAbbr", generalFunc.getJsonValueStr("tDisplayTimeAbbr", obj_temp));
                            map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));

                            list.add(map);
                        }
                    }


                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }

                    boolean isAdapterRangeChanged = false;
                    if (list.size() - arr_notifications.length() != 0) {
                        isAdapterRangeChanged = true;
                    }
                    if (isAdapterRangeChanged) {
                        notificationAdapter.notifyItemRangeChanged((list.size() - arr_notifications.length()), arr_notifications.length());
                    } else {
                        notificationAdapter.notifyDataSetChanged();
                    }
                } else {
                    if (list.size() == 0) {
                        removeNextPageConfig();
                        binding.noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                        binding.noDataTxt.setVisibility(View.VISIBLE);
                        notificationAdapter.notifyDataSetChanged();
                    }
                }
            } else {
                if (!isLoadMore) {
                    removeNextPageConfig();
                    generateErrorView();
                }
            }
        });
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        mIsLoading = false;
        isNextPageAvailable = false;
    }

    private void generateErrorView() {
        binding.errorView.setVisibility(View.VISIBLE);
        generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        binding.errorView.setOnRetryListener(() -> getNotificationDetails(false));
    }
}