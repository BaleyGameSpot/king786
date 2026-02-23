package com.fragments;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.core.widget.NestedScrollView;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.act.BookingActivity;
import com.act.CommonDeliveryTypeSelectionActivity;
import com.act.HelpMainCategory23Pro;
import com.act.UberXHomeActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.FoodRatingActivity;
import com.act.deliverAll.OrderDetailsActivity;
import com.act.deliverAll.ServiceHomeActivity;
import com.act.deliverAll.TrackOrderActivity;
import com.adapter.files.deliverAll.ActiveOrderAdapter;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
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

/**
 * A simple {@link Fragment} subclass.
 */
public class OrderFragment extends BaseFragment implements ActiveOrderAdapter.OnItemClickListener, SwipeRefreshLayout.OnRefreshListener {

    View view;
    MTextView noRidesTxt;
    public MTextView filterTxt;
    RecyclerView activeRecyclerView;
    ErrorView errorView;
    ActiveOrderAdapter activeOrderAdapter;
    ArrayList<HashMap<String, String>> list;
    boolean mIsLoading = false, isNextPageAvailable = false;
    GeneralFunctions generalFunc;

    private String next_page_str = "";
    ArrayList<HashMap<String, String>> orderFilterlist = new ArrayList<>();
    private LinearLayout filterArea;

    BookingActivity bookingAct;
    MyBookingFragment myBookingFragment;

    private SwipeRefreshLayout swipeRefreshLayout;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        view = inflater.inflate(R.layout.fragment_booking, container, false);

        noRidesTxt = (MTextView) view.findViewById(R.id.noRidesTxt);
        activeRecyclerView = (RecyclerView) view.findViewById(R.id.myBookingsRecyclerView);
        errorView = (ErrorView) view.findViewById(R.id.errorView);

        try {
            if (getActivity() instanceof UberXHomeActivity) {
                myBookingFragment = ((UberXHomeActivity) getActivity()).myBookingFragment;
            } else if (getActivity() instanceof CommonDeliveryTypeSelectionActivity) {
                myBookingFragment = ((CommonDeliveryTypeSelectionActivity) getActivity()).myBookingFragment;
            } else if (getActivity() instanceof FoodDeliveryHomeActivity) {
                myBookingFragment = ((FoodDeliveryHomeActivity) getActivity()).myBookingFragment;
            } else if (getActivity() instanceof ServiceHomeActivity) {
                myBookingFragment = ((ServiceHomeActivity) getActivity()).myBookingFragment;
            } else {
                bookingAct = (BookingActivity) getActivity();
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

        filterTxt = (MTextView) view.findViewById(R.id.filterTxt);

        filterArea = (LinearLayout) view.findViewById(R.id.filterArea);
        addToClickHandler(filterArea);

        swipeRefreshLayout = (SwipeRefreshLayout) view.findViewById(R.id.swipe_refresh_layout);
        swipeRefreshLayout.setOnRefreshListener(this);

        generalFunc = MyApp.getInstance().getGeneralFun(getActivity());

        list = new ArrayList<>();
        activeOrderAdapter = new ActiveOrderAdapter(getActivity(), list, generalFunc, false);
        activeRecyclerView.setAdapter(activeOrderAdapter);
        activeOrderAdapter.setOnItemClickListener(this);

        NestedScrollView nestedScrollView = view.findViewById(R.id.nestedScrollView);
        nestedScrollView.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {

            View childView = v.getChildAt(v.getChildCount() - 1);

            if (childView != null) {

                if ((scrollY >= (childView.getMeasuredHeight() - v.getMeasuredHeight())) &&
                        scrollY > oldScrollY) {

                    int visibleItemCount = activeRecyclerView.getLayoutManager().getChildCount();
                    int totalItemCount = activeRecyclerView.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) activeRecyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
//                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                        mIsLoading = true;
                        activeOrderAdapter.addFooterView();
                        getActiveOrderHistory(true);
                    } else if (!isNextPageAvailable) {
                        activeOrderAdapter.removeFooterView();
                    }
                }
            }
        });

        return view;
    }

    @Override
    public void onRefresh() {
        swipeRefreshLayout.setRefreshing(true);
        getActiveOrderHistory(false);
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        if (view.getId() == R.id.filterArea) {
            if (myBookingFragment != null) {
                myBookingFragment.BuildType("Order");
            } else {
                bookingAct.BuildType("Order");
            }
        }
    }


    @Override
    public void onResume() {
        super.onResume();
        if (myBookingFragment != null && myBookingFragment.filterImageview != null) {
            myBookingFragment.filterImageview.setVisibility(View.GONE);
        } else if (bookingAct != null && bookingAct.filterImageview != null) {
            bookingAct.filterImageview.setVisibility(View.GONE);
        }
        if (myBookingFragment != null && myBookingFragment.fragmentList != null && myBookingFragment.fragmentList.size() > 0 && myBookingFragment.fragmentList.get(myBookingFragment.appLogin_view_pager.getCurrentItem()).getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("DisplayActiveOrder")) {
            getActiveOrderHistory(false);
        } else if (bookingAct != null && bookingAct.fragmentList != null && bookingAct.fragmentList.size() > 0 && bookingAct.fragmentList.get(bookingAct.appLogin_view_pager.getCurrentItem()).getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("DisplayActiveOrder")) {
            getActiveOrderHistory(false);
        }
    }

    private void getActiveOrderHistory(final boolean isLoadMore) {
        swipeRefreshLayout.setEnabled(false);
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }

        if (!isLoadMore) {

            SkeletonView(true);
            filterArea.setVisibility(View.GONE);

            removeNextPageConfig();
            list.clear();
            activeOrderAdapter.notifyDataSetChanged();
        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DisplayActiveOrder");
        parameters.put("UserType", Utils.userType);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("eSystem", Utils.eSystem_Type);

        Logger.d("Log_eSystem", "::" + Utils.eSystem_Type);

        if (myBookingFragment != null) {
            parameters.put("vSubFilterParam", myBookingFragment.selOrderSubFilterType);
        } else {
            parameters.put("vSubFilterParam", bookingAct.selOrderSubFilterType);
        }
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }
        noRidesTxt.setVisibility(View.GONE);

        ApiHandler.execute(getActivity(), parameters, responseString -> {
            swipeRefreshLayout.setEnabled(true);
            SkeletonView(false);

            noRidesTxt.setVisibility(View.GONE);
            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            swipeRefreshLayout.setRefreshing(false);

            if (responseObj != null && !responseObj.toString().equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseObj);

                    JSONArray message_arr = generalFunc.getJsonArray(Utils.message_str, responseObj);

                    if (!isLoadMore) {
                        list.clear();
                    }
                    if (message_arr != null) {
                        int messageArrSize = message_arr.length();
                        if (messageArrSize > 0) {
                            int user_profile_icon_size_main = 100;
                            String LBL_HISTORY_REST_DELIVERED = generalFunc.retrieveLangLBl("", "LBL_HISTORY_REST_DELIVERED");
                            String LBL_REFUNDED = generalFunc.retrieveLangLBl("", "LBL_REFUNDED");
                            String LBL_HISTORY_REST_CANCELLED = generalFunc.retrieveLangLBl("", "LBL_HISTORY_REST_CANCELLED");
                            String LBL_TOTAL_TXT = generalFunc.retrieveLangLBl("", "LBL_Total_Fare");
                            String LBL_ORDER_AT = generalFunc.retrieveLangLBl("", "LBL_ORDER_AT");
                            String LBL_HELP = generalFunc.retrieveLangLBl("", "LBL_HELP");
                            String LBL_VIEW_DETAILS = generalFunc.retrieveLangLBl("", "LBL_VIEW_DETAILS");
                            String LBL_TRACK_ORDER = generalFunc.retrieveLangLBl("", "LBL_TRACK_ORDER");
                            String LBL_ORDER_AMOUNT_TXT = generalFunc.retrieveLangLBl("", "LBL_ORDER_AMOUNT_TXT");

                            for (int i = 0; i < messageArrSize; i++) {
                                JSONObject addr_obj = generalFunc.getJsonObject(message_arr, i);
                                HashMap<String, String> map = new HashMap<>();
                                map.put("vCompany", generalFunc.getJsonValueStr("vCompany", addr_obj));
                                map.put("vRestuarantLocation", generalFunc.getJsonValueStr("vRestuarantLocation", addr_obj));
                                map.put("iOrderId", generalFunc.getJsonValueStr("iOrderId", addr_obj));
                                map.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", addr_obj));
                                map.put("vOrderNo", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vOrderNo", addr_obj)));

                                /*String tOrderRequestDate = generalFunc.getJsonValueStr("tOrderRequestDate", addr_obj);
                                try {
                                    map.put("ConvertedOrderRequestDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tOrderRequestDate, Utils.OriginalDateFormate, DateTimeUtils.BookingDateTimeFormat)));
                                    map.put("ConvertedOrderRequestTime", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tOrderRequestDate, Utils.OriginalDateFormate, DateTimeUtils.TimeFormat)));
                                } catch (Exception e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                    map.put("ConvertedOrderRequestDate", "");
                                    map.put("ConvertedOrderRequestTime", "");
                                }*/

                                map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", addr_obj));
                                map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", addr_obj));
                                map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", addr_obj));
                                map.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", addr_obj));
                                map.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", addr_obj));

                                map.put("fNetTotal", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fNetTotal", addr_obj)));
                                map.put("iCompanyId", generalFunc.getJsonValueStr("iCompanyId", addr_obj));
                                map.put("vStatus", generalFunc.getJsonValueStr("vStatus", addr_obj));
                                map.put("iStatusCode", generalFunc.getJsonValueStr("iStatusCode", addr_obj));
                                map.put("DisplayLiveTrack", generalFunc.getJsonValueStr("DisplayLiveTrack", addr_obj));
                                map.put("vServiceCategoryName", generalFunc.getJsonValueStr("vServiceCategoryName", addr_obj));
                                map.put("vOrderStatus", generalFunc.getJsonValueStr("vOrderStatus", addr_obj));
                                map.put("vImage", Utils.getResizeImgURL(getActivity(), generalFunc.getJsonValueStr("vImage", addr_obj), user_profile_icon_size_main, user_profile_icon_size_main));

                                map.put("driverName", generalFunc.getJsonValueStr("driverName", addr_obj));
                                map.put("isRatingButtonShow", generalFunc.getJsonValueStr("isRatingButtonShow", addr_obj));
                                map.put("ENABLE_FOOD_RATING_DETAIL_FLOW", generalFunc.getJsonValueStr("ENABLE_FOOD_RATING_DETAIL_FLOW", addr_obj));
                                map.put("eTakeaway", generalFunc.getJsonValueStr("eTakeaway", addr_obj));

                                map.put("LBL_HISTORY_REST_DELIVERED", LBL_HISTORY_REST_DELIVERED);
                                map.put("LBL_REFUNDED", LBL_REFUNDED);
                                map.put("LBL_HISTORY_REST_CANCELLED", LBL_HISTORY_REST_CANCELLED);
                                map.put("LBL_TOTAL_TXT", LBL_TOTAL_TXT);
                                map.put("LBL_ORDER_AT", LBL_ORDER_AT);
                                map.put("LBL_HELP", LBL_HELP);
                                map.put("LBL_VIEW_DETAILS", LBL_VIEW_DETAILS);
                                map.put("LBL_TRACK_ORDER", LBL_TRACK_ORDER);
                                map.put("LBL_ORDER_AMOUNT_TXT", LBL_ORDER_AMOUNT_TXT);

                                list.add(map);
                            }
                        }
                    }

                    buildFilterTypes(responseObj);
                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                    activeOrderAdapter.notifyDataSetChanged();
                } else {
                    buildFilterTypes(responseObj);
                    if (list.size() == 0) {
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                        noRidesTxt.setVisibility(View.VISIBLE);
                    }
                    removeNextPageConfig();
                }
            } else {
                if (!isLoadMore) {
                    buildFilterTypes(responseObj);
                    removeNextPageConfig();
                    generateErrorView();
                }
            }
            filterArea.setVisibility(View.VISIBLE);
            mIsLoading = false;
        });

    }

    private void SkeletonView(boolean isShow) {

        if ((myBookingFragment != null && myBookingFragment.fragmentList.get(myBookingFragment.appLogin_view_pager.getCurrentItem())
                .getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("DisplayActiveOrder"))

                || (bookingAct != null && bookingAct.fragmentList.get(bookingAct.appLogin_view_pager.getCurrentItem())
                .getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("DisplayActiveOrder"))) {

            if (isShow) {
                SkeletonViewHandler.getInstance().showListSkeletonView(activeRecyclerView, R.layout.skeleton_your_bookings, activeOrderAdapter);
            } else {
                SkeletonViewHandler.getInstance().hideSkeletonView();
            }
        }
    }

    private void buildFilterTypes(JSONObject responseObj) {
        if (responseObj == null)
            return;
        JSONArray orderStatusFilterArr = generalFunc.getJsonArray("subFilterOption", responseObj);
        String eFilterSel = generalFunc.getJsonValueStr("eFilterSel", responseObj);

        if (orderStatusFilterArr != null) {
            int orderStatusFilterArrSize = orderStatusFilterArr.length();
            if (orderStatusFilterArrSize > 0) {
                orderFilterlist = new ArrayList<>();
                for (int i = 0; i < orderStatusFilterArrSize; i++) {
                    JSONObject obj_temp = generalFunc.getJsonObject(orderStatusFilterArr, i);
                    HashMap<String, String> map = new HashMap<>();
                    String vTitle = generalFunc.getJsonValueStr("vTitle", obj_temp);
                    map.put("vTitle", vTitle);
                    String vSubFilterParam = generalFunc.getJsonValueStr("vSubFilterParam", obj_temp);
                    map.put("vSubFilterParam", vSubFilterParam);

                    if (vSubFilterParam.equalsIgnoreCase(eFilterSel)) {
                        if (myBookingFragment != null) {
                            myBookingFragment.selOrderSubFilterType = vSubFilterParam;
                            myBookingFragment.orderSubFilterPosition = i;
                        } else {
                            bookingAct.selOrderSubFilterType = vSubFilterParam;
                            bookingAct.orderSubFilterPosition = i;
                        }
                        filterTxt.setText(vTitle);
                    }
                    orderFilterlist.add(map);
                }
                if (myBookingFragment != null) {
                    myBookingFragment.subFilterManage(orderFilterlist, "Order");
                } else {
                    bookingAct.subFilterManage(orderFilterlist, "Order");
                }
            }
        }
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        activeOrderAdapter.removeFooterView();
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getActiveOrderHistory(false));
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == Activity.RESULT_OK) {
            next_page_str = "2";
            list.clear();
            getActiveOrderHistory(true);
        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        Utils.hideKeyboard(getActivity());
    }

    @Override
    public void onItemClickList(View v, int position, String isSelect) {
        HashMap<String, String> listData = list.get(position);
        if (isSelect.equalsIgnoreCase("help")) {
            Bundle bn = new Bundle();
            bn.putString("iOrderId", listData.get("iOrderId"));
            new ActUtils(getActivity()).startActWithData(HelpMainCategory23Pro.class, bn);
        } else if (isSelect.equalsIgnoreCase("track")) {
            Bundle bn = new Bundle();
            bn.putString("iOrderId", listData.get("iOrderId"));
            bn.putString("iServiceId", listData.get("iServiceId"));
            new ActUtils(getActivity()).startActWithData(TrackOrderActivity.class, bn);
        } else if (isSelect.equalsIgnoreCase("view")) {
            Bundle bn = new Bundle();
            bn.putString("iOrderId", listData.get("iOrderId"));
            bn.putString("iServiceId", listData.get("iServiceId"));
            new ActUtils(getActivity()).startActWithData(OrderDetailsActivity.class, bn);
        } else if (isSelect.equalsIgnoreCase("rating")) {
            Bundle bn = new Bundle();
            if (list.get(position).get("ENABLE_FOOD_RATING_DETAIL_FLOW").equalsIgnoreCase("Yes")) {
                bn.putBoolean("IS_NEW", true);
            } else {
                bn.putBoolean("IS_NEW", false);
            }
            bn.putString("iOrderId", listData.get("iOrderId"));
            bn.putString("vOrderNo", listData.get("vOrderNo"));
            bn.putString("driverName", listData.get("driverName"));
            bn.putString("vCompany", listData.get("vCompany"));
            bn.putString("eTakeaway", listData.get("eTakeaway"));
            new ActUtils(getActivity()).startActWithData(FoodRatingActivity.class, bn);
        }
    }
}