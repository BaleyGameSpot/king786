package com.fragments;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.act.BiddingHistoryDetailActivity;
import com.act.BiddingTaskActivity;
import com.act.BookingActivity;
import com.act.CommonDeliveryTypeSelectionActivity;
import com.act.HistoryDetailActivity;
import com.act.OnGoingTripDetailsActivity;
import com.act.RideDeliveryActivity;
import com.act.UberXHomeActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.ServiceHomeActivity;
import com.adapter.files.AllBiddingRecycleAdapter;
import com.dialogs.OpenListView;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

/**
 * A simple {@link Fragment} subclass.
 */
public class BiddingBookingFragment extends BaseFragment implements AllBiddingRecycleAdapter.OnItemClickListener, SwipeRefreshLayout.OnRefreshListener {

    GeneralFunctions generalFunc;
    View view;
    MTextView noRidesTxt;
    public MTextView filterTxt;
    RecyclerView historyRecyclerView;
    ErrorView errorView;
    AllBiddingRecycleAdapter historyRecyclerAdapter;
    ArrayList<HashMap<String, String>> list;
    ArrayList<HashMap<String, String>> subFilterlist = new ArrayList<>();
    boolean mIsLoading = false, isNextPageAvailable = false;
    String next_page_str = "";
    int HISTORYDETAILS = 1, selPos = -1, selCurrentPosition = -1;
    private LinearLayout filterArea;

    BookingActivity bookingAct;
    MyBookingFragment myBookingFragment;
    private SwipeRefreshLayout swipeRefreshLayout;
    AlertDialog dialog_declineOrder;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        view = inflater.inflate(R.layout.fragment_bidding, container, false);

        noRidesTxt = (MTextView) view.findViewById(R.id.noRidesTxt);
        filterTxt = (MTextView) view.findViewById(R.id.filterTxt);
        historyRecyclerView = (RecyclerView) view.findViewById(R.id.myBookingsRecyclerView);
        errorView = (ErrorView) view.findViewById(R.id.errorView);

        if (getActivity() instanceof BookingActivity) {
            bookingAct = (BookingActivity) getActivity();
        } else {
            if (getActivity() instanceof UberXHomeActivity) {
                myBookingFragment = ((UberXHomeActivity) getActivity()).myBookingFragment;
            } else if (getActivity() instanceof ServiceHomeActivity) {
                myBookingFragment = ((ServiceHomeActivity) getActivity()).myBookingFragment;
            } else if (getActivity() instanceof CommonDeliveryTypeSelectionActivity) {
                myBookingFragment = ((CommonDeliveryTypeSelectionActivity) getActivity()).myBookingFragment;
            } else if (getActivity() instanceof RideDeliveryActivity) {
                myBookingFragment = ((RideDeliveryActivity) getActivity()).myBookingFragment;
            } else if (getActivity() instanceof FoodDeliveryHomeActivity) {
                myBookingFragment = ((FoodDeliveryHomeActivity) getActivity()).myBookingFragment;
            }
        }


        generalFunc = MyApp.getInstance().getGeneralFun(getActivity());

        filterArea = (LinearLayout) view.findViewById(R.id.filterArea);
        addToClickHandler(filterArea);

        swipeRefreshLayout = (SwipeRefreshLayout) view.findViewById(R.id.swipe_refresh_layout);
        swipeRefreshLayout.setOnRefreshListener(this);

        list = new ArrayList<>();
        historyRecyclerAdapter = new AllBiddingRecycleAdapter(requireActivity(), list, generalFunc, false);
        historyRecyclerView.setAdapter(historyRecyclerAdapter);
        historyRecyclerAdapter.setOnItemClickListener(this);

        NestedScrollView nestedScrollView = view.findViewById(R.id.nestedScrollView);

        nestedScrollView.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {

            View getChild = v.getChildAt(v.getChildCount() - 1);
            if (getChild != null) {
                if ((scrollY >= (getChild.getMeasuredHeight() - v.getMeasuredHeight())) && scrollY > oldScrollY) {

                    int visibleItemCount = Objects.requireNonNull(historyRecyclerView.getLayoutManager()).getChildCount();
                    int totalItemCount = historyRecyclerView.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) historyRecyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                        mIsLoading = true;
                        historyRecyclerAdapter.addFooterView();
                        historyRecyclerView.stopScroll();
                        getBiddingPosts(true);

                    } else if (!isNextPageAvailable) {
                        historyRecyclerAdapter.removeFooterView();
                    }
                }
            }
        });

        return view;
    }

    @Override
    public void onResume() {
        super.onResume();
        if (myBookingFragment != null) {
            myBookingFragment.filterImageview.setVisibility(View.GONE);
        } else if (bookingAct != null && bookingAct.filterImageview != null) {
            bookingAct.filterImageview.setVisibility(View.GONE);
        }
        if (myBookingFragment != null && myBookingFragment.fragmentList != null && myBookingFragment.fragmentList.size() > 0 && myBookingFragment.fragmentList.get(myBookingFragment.appLogin_view_pager.getCurrentItem()).getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("getBiddingPosts")) {
            getBiddingPosts(false);
        } else if (bookingAct != null && bookingAct.fragmentList != null && bookingAct.fragmentList.size() > 0 && bookingAct.fragmentList.get(bookingAct.appLogin_view_pager.getCurrentItem()).getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("getBiddingPosts")) {
            getBiddingPosts(false);
        }
    }

    @Override
    public void onItemClickList(View v, int position, String type) {
        HashMap<String, String> listData = list.get(position);
        if (type.equalsIgnoreCase("ViewBidding")) {
            Bundle bn = new Bundle();
            bn.putString("iBiddingPostId", listData.get("iBiddingPostId"));
            new ActUtils(getActivity()).startActWithData(BiddingTaskActivity.class, bn);
        } else if (type.equalsIgnoreCase("ShowDetail")) {
            Utils.hideKeyboard(getActivity());
            Bundle bn = new Bundle();
            bn.putString("iTripId", listData.get("iTripId"));
            bn.putString("iCabBookingId", listData.get("iCabBookingId"));
            new ActUtils(getActivity()).startActForResult(HistoryDetailActivity.class, bn, HISTORYDETAILS);
        } else if (type.equalsIgnoreCase("ViewDetail")) {
            Bundle bn = new Bundle();
            if (list.get(position).get("bidding_statusOrg").equalsIgnoreCase("In process")) {
                bn.putString("iBiddingPostId", list.get(position).get("iBiddingPostId"));
                bn.putString("eType", "Bidding");
                bn.putBoolean("isBid", true);
                bn.putBoolean("isBack", true);
                new ActUtils(getActivity()).startActForResult(OnGoingTripDetailsActivity.class, bn, Utils.LIVE_TRACK_REQUEST_CODE);
            } else if (list.get(position).get("bidding_statusOrg").equalsIgnoreCase("Completed")) {
                bn.putString("iBiddingPostId", list.get(position).get("iBiddingPostId"));
                new ActUtils(getActivity()).startActWithData(BiddingHistoryDetailActivity.class, bn);
            } else {
                bn.putString("iBiddingPostId", listData.get("iBiddingPostId"));
                new ActUtils(getActivity()).startActWithData(BiddingTaskActivity.class, bn);
            }
        } else if (type.equalsIgnoreCase("CancelBidding")) {
            selPos = position;
            showDeclineReasonsAlert();
        }
    }

    private void showDeclineReasonsAlert() {
        if (dialog_declineOrder != null) {
            if (dialog_declineOrder.isShowing()) {
                dialog_declineOrder.dismiss();
            }
            dialog_declineOrder = null;
        }
        selCurrentPosition = -1;

        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(requireActivity());
        // builder.setTitle(titleDailog);

        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = (MaterialEditText) dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = (RelativeLayout) dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        int size10sdp = (int) getResources().getDimension(R.dimen._10sdp);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, size10sdp, 0);
        } else {
            reasonBox.setPaddings(size10sdp, 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));

        ArrayList<HashMap<String, String>> sub_list = new ArrayList<>();


        MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);
        MTextView submitTxt = (MTextView) dialogView.findViewById(R.id.submitTxt);
        MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);
        ImageView cancelImg = (ImageView) dialogView.findViewById(R.id.cancelImg);
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        subTitleTxt.setText(generalFunc.retrieveLangLBl("Cancel Bidding", "LBL_CANCEL_BIDDING"));

        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));
        MTextView declinereasonBox = (MTextView) dialogView.findViewById(R.id.declinereasonBox);
        declinereasonBox.setText("-- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_CANCEL_REASON") + " --");
        submitTxt.setClickable(false);
        submitTxt.setTextColor(getResources().getColor(R.color.gray_holo_light));

        submitTxt.setOnClickListener(v -> {
            if (selCurrentPosition == -1) {
                return;
            }
            if (!Utils.checkText(reasonBox) && selCurrentPosition == (sub_list.size() - 1)) {
                errorTextView.setVisibility(View.VISIBLE);
                errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                return;
            }
            cancelBooking(this.list.get(selPos).get("iBiddingPostId"), sub_list.get(selCurrentPosition).get("id"), reasonBox.getText().toString().trim());
            dialog_declineOrder.dismiss();
        });
        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getContext());
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });

        cancelImg.setOnClickListener(v -> {
            Utils.hideKeyboard(getContext());
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });

        declinereasonBox.setOnClickListener(v -> {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "GetCancelReasons");
            parameters.put("iMemberId", generalFunc.getMemberId());
            parameters.put("eUserType", Utils.app_type);
            parameters.put("eJobType", list.get(selPos).get("eJobType"));

            parameters.put("iBiddingPostId", list.get(selPos).get("iBiddingPostId"));

            ApiHandler.execute(getActivity(), parameters, true, false, generalFunc, responseString -> {
                sub_list.clear();
                if (Utils.checkText(responseString)) {

                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
                        if (arr_msg != null) {
                            int arrSize = arr_msg.length();
                            for (int i = 0; i < arrSize; i++) {
                                JSONObject obj_tmp = generalFunc.getJsonObject(arr_msg, i);
                                HashMap<String, String> datamap = new HashMap<>();
                                datamap.put("title", generalFunc.getJsonValueStr("vTitle", obj_tmp));
                                datamap.put("id", generalFunc.getJsonValueStr("iCancelReasonId", obj_tmp));
                                sub_list.add(datamap);
                            }

                            HashMap<String, String> othermap = new HashMap<>();
                            othermap.put("title", generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
                            othermap.put("id", "");
                            sub_list.add(othermap);

                            OpenListView.getInstance(getActivity(), generalFunc.retrieveLangLBl("", "LBL_SELECT_REASON"), sub_list, OpenListView.OpenDirection.CENTER, true, position -> {
                                selCurrentPosition = position;
                                HashMap<String, String> mapData = sub_list.get(position);
                                errorTextView.setVisibility(View.GONE);
                                declinereasonBox.setText(mapData.get("title"));
                                if (selCurrentPosition == (sub_list.size() - 1)) {
                                    reasonBox.setVisibility(View.VISIBLE);
                                    commentArea.setVisibility(View.VISIBLE);
                                } else {
                                    commentArea.setVisibility(View.GONE);
                                    reasonBox.setVisibility(View.GONE);
                                }
                                submitTxt.setClickable(true);
                                submitTxt.setTextColor(getResources().getColor(R.color.white));
                            }).show(selCurrentPosition, "title");
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
                        }
                    } else {
                        String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                        if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY)
                                || message.equals("LBL_SERVER_COMM_ERROR")) {

                            MyApp.getInstance().restartWithGetDataApp();
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                        }
                    }
                } else {
                    generalFunc.showError();
                }
            });
        });
        dialog_declineOrder = builder.create();
        dialog_declineOrder.setCancelable(false);
        dialog_declineOrder.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(requireActivity(), R.drawable.all_roundcurve_card));
        dialog_declineOrder.show();
    }

    private void cancelBooking(String iBiddingPostId, String iCancelReasonId, String reason) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "cancelBidding");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iBiddingPostId", iBiddingPostId);
        parameters.put("iCancelReasonId", iCancelReasonId);
        parameters.put("Reason", reason);

        ApiHandler.execute(getActivity(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    list.clear();
                    historyRecyclerAdapter.notifyDataSetChanged();
                    getBiddingPosts(false);
                }
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            } else {
                generalFunc.showError();
            }
        });

    }

    @Override
    public void onViewServiceClickList(View v, int position) {
    }

    @Override
    public void onRefresh() {
        swipeRefreshLayout.setRefreshing(false);
        getBiddingPosts(false);
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        if (view.getId() == R.id.filterArea) {
            if (myBookingFragment != null) {
                myBookingFragment.BuildType("Bidding");
            } else {
                bookingAct.BuildType("Bidding");
            }
        }
    }


    private void getBiddingPosts(final boolean isLoadMore) {
        swipeRefreshLayout.setEnabled(false);
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }

        if (!isLoadMore) {
            SkeletonView(true);
            filterArea.setVisibility(View.GONE);

            removeNextPageConfig();
            list.clear();
            historyRecyclerAdapter.notifyDataSetChanged();
        }
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getBiddingPosts");
        parameters.put("memberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        if (myBookingFragment != null) {
            parameters.put("vSubFilterParam", myBookingFragment.selBiddingSubFilterType);
        } else {
            parameters.put("vSubFilterParam", bookingAct.selBiddingSubFilterType);
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
                    if (!isLoadMore) {

                        list.clear();
                    }
                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseObj);
                    JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseObj);
                    if (arr_rides != null) {
                        int arrRidesSize = arr_rides.length();

                        if (arr_rides.length() > 0) {
                            String LBL_TASK_TXT = generalFunc.retrieveLangLBl("", "LBL_TASK_TXT");
                            String LBL_CANCEL_BIDDING = generalFunc.retrieveLangLBl("", "LBL_CANCEL_BIDDING");
                            String LBL_BIDDING_SERVICE_ADDRESS_TXT = generalFunc.retrieveLangLBl("", "LBL_BIDDING_SERVICE_ADDRESS_TXT");
                            String LBL_VIEW_TASK_BIDDING = generalFunc.retrieveLangLBl("", "LBL_VIEW_TASK_BIDDING");
                            String LBL_VIEW_DETAILS = generalFunc.retrieveLangLBl("", "LBL_VIEW_DETAILS");

                            for (int i = 0; i < arrRidesSize; i++) {
                                JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);
                                HashMap<String, String> map = new HashMap<>();

                                map.put("eJobType", generalFunc.getJsonValueStr("eJobType", obj_temp));

                                map.put("vContactName", generalFunc.getJsonValueStr("vContactName", obj_temp));
                                map.put("vBiddingPostNo", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vBiddingPostNo", obj_temp)));
                                map.put("fBiddingAmount", generalFunc.getJsonValueStr("fBiddingAmount", obj_temp));
                                map.put("tDescription", generalFunc.getJsonValueStr("tDescription", obj_temp));
                                map.put("eStatus", generalFunc.getJsonValueStr("eStatus", obj_temp));
                                map.put("bidding_status", generalFunc.getJsonValueStr("bidding_status", obj_temp));
                                map.put("bidding_statusOrg", generalFunc.getJsonValueStr("bidding_statusOrg", obj_temp));
                                map.put("iBiddingPostId", generalFunc.getJsonValueStr("iBiddingPostId", obj_temp));
                                map.put("showDetailBtn", generalFunc.getJsonValueStr("showDetailBtn", obj_temp));
                                map.put("showCancelBtn", generalFunc.getJsonValueStr("showCancelBtn", obj_temp));
                                map.put("showBiddingTaskBtn", generalFunc.getJsonValueStr("showBiddingTaskBtn", obj_temp));
                                map.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
                                map.put("vServiceAddress", generalFunc.getJsonValueStr("vServiceAddress", obj_temp));
                                map.put("dBiddingDate", generalFunc.getJsonValueStr("dBiddingDate", obj_temp));
                                map.put("biddingDetails", generalFunc.getJsonValueStr("biddingDetails", obj_temp));
                                map.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
                                map.put("vStatus_BG_color", generalFunc.getJsonValueStr("vStatus_BG_color", obj_temp));
                                map.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));
                                map.put("driverFullName", generalFunc.getJsonValueStr("driverFullName", obj_temp));
                                map.put("driverRating", generalFunc.getJsonValueStr("vAvgRating", obj_temp));
                                map.put("LBL_TASK_TXT", LBL_TASK_TXT);
                                map.put("LBL_BIDDING_SERVICE_ADDRESS_TXT", LBL_BIDDING_SERVICE_ADDRESS_TXT);
                                map.put("LBL_CANCEL_BIDDING", LBL_CANCEL_BIDDING);
                                map.put("LBL_VIEW_TASK_BIDDING", LBL_VIEW_TASK_BIDDING);
                                map.put("LBL_VIEW_DETAILS", LBL_VIEW_DETAILS);

                                /*try {
                                    String dBiddingDate = generalFunc.getJsonValueStr("dBiddingDate", obj_temp);
                                    map.put("ConvertedTripRequestDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBiddingDate, Utils.OriginalDateFormate, DateTimeUtils.BookingDateTimeFormat)));
                                    map.put("ConvertedTripRequestTime", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBiddingDate, Utils.OriginalDateFormate, DateTimeUtils.TimeFormat)));
                                } catch (Exception e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                    map.put("ConvertedTripRequestDate", "");
                                    map.put("ConvertedTripRequestTime", "");
                                }*/
                                map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                                map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_temp));
                                map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));

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
                    historyRecyclerAdapter.notifyDataSetChanged();
                } else {
                    buildFilterTypes(responseObj);
                    if (list.size() == 0) {
                        removeNextPageConfig();
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                        noRidesTxt.setVisibility(View.VISIBLE);
                    }
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
                .getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("getBiddingPosts"))

                || (bookingAct != null && bookingAct.fragmentList.get(bookingAct.appLogin_view_pager.getCurrentItem())
                .getArguments().getString("HISTORY_TYPE", "").equalsIgnoreCase("getBiddingPosts"))) {

            if (isShow) {
                SkeletonViewHandler.getInstance().showListSkeletonView(historyRecyclerView, R.layout.skeleton_your_bookings, historyRecyclerAdapter);
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
                subFilterlist = new ArrayList<>();
                for (int i = 0; i < orderStatusFilterArrSize; i++) {
                    JSONObject obj_temp = generalFunc.getJsonObject(orderStatusFilterArr, i);
                    HashMap<String, String> map = new HashMap<>();
                    String vTitle = generalFunc.getJsonValueStr("vTitle", obj_temp);
                    map.put("vTitle", vTitle);
                    String vSubFilterParam = generalFunc.getJsonValueStr("vSubFilterParam", obj_temp);
                    map.put("vSubFilterParam", vSubFilterParam);

                    if (vSubFilterParam.equalsIgnoreCase(eFilterSel)) {
                        if (myBookingFragment != null) {
                            myBookingFragment.selBiddingSubFilterType = vSubFilterParam;
                            myBookingFragment.biddingSubFilterPosition = i;
                        } else {
                            bookingAct.selOrderSubFilterType = vSubFilterParam;
                            bookingAct.orderSubFilterPosition = i;
                        }
                        filterTxt.setText(vTitle);
                    }
                    subFilterlist.add(map);
                }
                if (myBookingFragment != null) {
                    myBookingFragment.subFilterManage(subFilterlist, "Bidding");
                } else {
                    bookingAct.subFilterManage(subFilterlist, "Bidding");
                }
            }
        }
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        historyRecyclerAdapter.removeFooterView();
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getBiddingPosts(false));
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == Activity.RESULT_OK) {
            list.clear();
            getBiddingPosts(true);
        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        Utils.hideKeyboard(getActivity());
    }
}