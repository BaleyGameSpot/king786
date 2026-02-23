package com.fragments;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.text.format.Time;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.core.widget.NestedScrollView;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.act.BookingsActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.deliverAll.LiveTaskListActivity;
import com.act.deliverAll.OrderDetailsActivity;
import com.adapter.files.deliverAll.OrderHistoryRecycleAdapter;
import com.datepicker.files.SlideDateTimeListener;
import com.datepicker.files.SlideDateTimePicker;
import com.general.DatePicker;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Locale;

/**
 * A simple {@link Fragment} subclass.
 */
public class OrderFragment extends BaseFragment implements OrderHistoryRecycleAdapter.OnItemClickListener, SwipeRefreshLayout.OnRefreshListener {

    private GeneralFunctions generalFunc;
    private JSONObject userProfileJsonObj;
    public MTextView filterTxt;
    private MTextView earningFareHTxt, earningFareVTxt, avgRatingCalcTxt, totalOrderHTxt, totalOrderVTxt, noOrdersTxt;
    private MaterialEditText fromDateEditBox, toDateEditBox;

    private boolean mIsLoading = false, isNextPageAvailable = false;

    private RecyclerView historyRecyclerView;
    private ErrorView errorView;

    OrderHistoryRecycleAdapter orderHistoryRecycleAdapter;
    ArrayList<HashMap<String, String>> listData = new ArrayList<>();
    ArrayList<HashMap<String, String>> subFilterlist = new ArrayList<>();

    private String next_page_str = "", fromSelectedTime = "", toSelectedTime = "", SELECTED_DATE = "";
    private Date fromDateDay = null, toDateDay = null;
    private View view;
    private BookingsActivity bookingAct;

    private SwipeRefreshLayout swipeRefreshLayout;
    private MyBookingFragment myBookingFragment = null;
    private ServerTask currentExeTask;

    private ImageView filterDropImg;

    private LinearLayout calenderHeaderLayout, titleContainerView, detailsArea, filterArea;
    private ArrayList<Calendar> calendarArrayList;
    private MTextView dateTitle;
    private Date mDate;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        view = inflater.inflate(R.layout.fragment_order, container, false);

        if (requireActivity() instanceof MainActivity) {
            myBookingFragment = ((MainActivity) requireActivity()).myBookingFragment;
        } else if (requireActivity() instanceof MainActivity_22) {
            myBookingFragment = ((MainActivity_22) requireActivity()).myBookingFragment;
        } else if (requireActivity() instanceof LiveTaskListActivity) {
            myBookingFragment = ((LiveTaskListActivity) requireActivity()).myBookingFragment;
        } else {
            myBookingFragment = null;
            bookingAct = (BookingsActivity) requireActivity();
        }

        generalFunc = MyApp.getInstance().getGeneralFun(requireActivity());

        userProfileJsonObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        filterDropImg = view.findViewById(R.id.filterDropImg);
        filterDropImg.setVisibility(View.GONE);

        addCalenderView();

        filterArea = view.findViewById(R.id.filterArea);
        addToClickHandler(filterArea);
        detailsArea = view.findViewById(R.id.detailsArea);
        swipeRefreshLayout = view.findViewById(R.id.swipe_refresh_layout);
        swipeRefreshLayout.setOnRefreshListener(this);

        fromDateEditBox = view.findViewById(R.id.fromDateEditBox);
        toDateEditBox = view.findViewById(R.id.toDateEditBox);
        historyRecyclerView = view.findViewById(R.id.historyRecyclerView);
        noOrdersTxt = view.findViewById(R.id.noOrdersTxt);
        filterTxt = view.findViewById(R.id.filterTxt);
        errorView = view.findViewById(R.id.errorView);

        ((MTextView) view.findViewById(R.id.avgRatingTxt)).setText(generalFunc.retrieveLangLBl("Avg. Rating", "LBL_AVG_RATING"));

        earningFareHTxt = view.findViewById(R.id.earningFareHTxt);
        earningFareVTxt = view.findViewById(R.id.earningFareVTxt);
        avgRatingCalcTxt = view.findViewById(R.id.avgRatingCalcTxt);
        totalOrderHTxt = view.findViewById(R.id.totalOrderHTxt);
        totalOrderVTxt = view.findViewById(R.id.totalOrderVTxt);

        orderHistoryRecycleAdapter = new OrderHistoryRecycleAdapter(getActContext(), listData, generalFunc, false);
        historyRecyclerView.setAdapter(orderHistoryRecycleAdapter);
        orderHistoryRecycleAdapter.setOnItemClickListener(this);
        historyRecyclerView.setNestedScrollingEnabled(false);

        NestedScrollView nestedScrollView = view.findViewById(R.id.nestedScrollView);
        nestedScrollView.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {
            if (v.getChildAt(v.getChildCount() - 1) != null) {

                if ((scrollY >= (v.getChildAt(v.getChildCount() - 1).getMeasuredHeight() - v.getMeasuredHeight())) &&
                        scrollY > oldScrollY) {

                    int visibleItemCount = historyRecyclerView.getLayoutManager().getChildCount();
                    int totalItemCount = historyRecyclerView.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) historyRecyclerView.getLayoutManager()).findFirstVisibleItemPosition();


                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                        mIsLoading = true;
                        orderHistoryRecycleAdapter.addFooterView();
                        getPastOrders(true, fromSelectedTime, toSelectedTime);
                    } else if (!isNextPageAvailable) {
                        orderHistoryRecycleAdapter.removeFooterView();
                    }
                }
            }
        });

        long fromDateMillis = System.currentTimeMillis() - (7 * 24 * 60 * 60 * 1000);
        Time fromDate = new Time();
        fromDate.set(fromDateMillis);

        fromDateDay = Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(fromDateMillis, "ddMMyyyy"));

        fromSelectedTime = getDateFromMilliSec(fromDateMillis, DateTimeUtils.DayFormatEN, Locale.US);
        fromDateEditBox.setText(getDateFromMilliSec(fromDateMillis, "dd MMM yyyy"));

        long toDateMillis = System.currentTimeMillis();
        Time toDate = new Time();
        toDate.set(toDateMillis);

        toDateDay = Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(toDateMillis, "ddMMyyyy"));
        toSelectedTime = getDateFromMilliSec(toDateMillis, DateTimeUtils.DayFormatEN, Locale.US);
        toDateEditBox.setText(getDateFromMilliSec(toDateMillis, "dd MMM yyyy"));

        fromDateEditBox.getLabelFocusAnimator().start();
        toDateEditBox.getLabelFocusAnimator().start();

        removeInput();
        setLabels();

        earningFareVTxt.setText("--");
        totalOrderVTxt.setText("--");
        avgRatingCalcTxt.setText("--");

        return view;
    }

    private void addCalenderView() {
        calenderHeaderLayout = view.findViewById(R.id.calenderHeaderLayout);
        calenderHeaderLayout.setVisibility(View.GONE);

        ImageView leftButton = view.findViewById(R.id.leftButton);
        ImageView rightButton = view.findViewById(R.id.rightButton);
        dateTitle = view.findViewById(R.id.dateTitle);

        Calendar now = Calendar.getInstance(MyUtils.getLocale());
        SimpleDateFormat currentDate2 = new SimpleDateFormat(DateTimeUtils.DateFormat, MyUtils.getLocale());
        dateTitle.setText(currentDate2.format(now.getTime()));

        SimpleDateFormat date_format = new SimpleDateFormat(DateTimeUtils.DayFormatEN, Locale.US);
        SELECTED_DATE = date_format.format(now.getTime());
        mDate = now.getTime();

        Date mSelectedCal = Utils.convertStringToDate(DateTimeUtils.DayFormatEN, generalFunc.getJsonValueStr("RegistrationDate", userProfileJsonObj));
        Calendar registrationDate = Calendar.getInstance();
        registrationDate.setTime(mSelectedCal);

        titleContainerView = view.findViewById(R.id.titleContainerView);
        titleContainerView.setOnClickListener(v -> openCalender(registrationDate));

        rightButton.setImageResource(generalFunc.isRTLmode() ? R.drawable.ic_left_arrow_circle : R.drawable.ic_right_arrow_circle);
        leftButton.setImageResource(generalFunc.isRTLmode() ? R.drawable.ic_right_arrow_circle : R.drawable.ic_left_arrow_circle);

        rightButton.setOnClickListener(v -> setNextPreviousDay(currentDate2, date_format, registrationDate, 1));
        leftButton.setOnClickListener(v -> setNextPreviousDay(currentDate2, date_format, registrationDate, -1));
    }

    private void setNextPreviousDay(SimpleDateFormat currentDate2, SimpleDateFormat date_format, Calendar registrationDate, int nextPrevious) {
        Calendar cal = DatePicker.checkNextPreviousDay(mDate, registrationDate, nextPrevious);
        if (cal == null) {
            return;
        }

        cal.add(Calendar.DATE, nextPrevious);

        SELECTED_DATE = date_format.format(cal.getTime());
        mDate = cal.getTime();
        dateTitle.setText(currentDate2.format(cal.getTime()));

        removeNextPageConfig();
        if (Utils.checkText(SELECTED_DATE)) {
            getPastOrders(false, fromSelectedTime, toSelectedTime);
        }
    }

    private Context getActContext() {
        if (myBookingFragment != null) {
            return myBookingFragment.getActContext();
        } else {
            return bookingAct.getActContext();
        }
    }

    private void setLabels() {
        fromDateEditBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_From"));
        toDateEditBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_TO"));
        earningFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOTAL_EARNINGS"));
        totalOrderHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOTAL_ORDERS"));
    }

    private String getDateFromMilliSec(long dateInMillis, String dateFormat) {
        String convertdate = "";
        SimpleDateFormat original_formate = new SimpleDateFormat(dateFormat);
        String dateString = original_formate.format(new Date(dateInMillis));
        SimpleDateFormat date_format = new SimpleDateFormat(dateFormat, MyUtils.getLocale());

        try {
            Date datedata = original_formate.parse(dateString);
            convertdate = date_format.format(datedata);
        } catch (ParseException e) {
            Logger.e("Exception", "::" + e.getMessage());
//            Logger.d("getDateFormatedType", "::" + e.toString());
        }
        return convertdate;
    }

    private String getDateFromMilliSec(long dateInMillis, String dateFormat, Locale locale) {
        SimpleDateFormat formatter = new SimpleDateFormat(dateFormat, locale);
        return formatter.format(new Date(dateInMillis));
    }

    @Override
    public void onResume() {
        super.onResume();
        if (myBookingFragment != null) {
            if (myBookingFragment.getId() != 0) {
                myBookingFragment.filterImageview.setVisibility(View.GONE);
            } else {
                return;
            }
        } else {
            if (bookingAct != null) {
                bookingAct.filterImageview.setVisibility(View.GONE);
            }
        }
        if (myBookingFragment != null && myBookingFragment.checkCurrentFragment(2) || bookingAct != null && bookingAct.checkCurrentFragment(2)) {
            getPastOrders(false, fromSelectedTime, toSelectedTime);
        }
        /*if (!isFirstInstance || (myBookingFragment != null && myBookingFragment.fragmentList.size() == 1)) {
            getPastOrders(false, fromSelectedTime, toSelectedTime);
        } else {
            isFirstInstance = !isFirstInstance;
        }*/
    }

    private void removeInput() {
        Utils.removeInput(fromDateEditBox);
        Utils.removeInput(toDateEditBox);

        fromDateEditBox.setOnTouchListener(new setOnTouchList());
        toDateEditBox.setOnTouchListener(new setOnTouchList());
        addToClickHandler(fromDateEditBox);
        addToClickHandler(toDateEditBox);
    }

    private void openFromDateSelection() {
        new SlideDateTimePicker.Builder(requireActivity().getSupportFragmentManager())
                .setListener(new SlideDateTimeListener() {
                    @Override
                    public void onDateTimeSet(Date date) {

                        if (Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(date.getTime(), "ddMMyyyy")).equals(toDateDay) || Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(date.getTime(), "ddMMyyyy")).before(toDateDay)) {

                            fromDateDay = Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(date.getTime(), "ddMMyyyy"));

                            fromSelectedTime = Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, date);

                            fromDateEditBox.setText(getDateFromMilliSec(date.getTime(), "dd MMM yyyy"));

                            getPastOrders(false, fromSelectedTime, toSelectedTime);
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_FROM_DATE_RESTRICT"));
                        }
                    }

                    @Override
                    public void onDateTimeCancel() {

                    }
                })
                .setTimePickerEnabled(false)
                .setInitialDate(new Date())
                .setMaxDate(new Date())
                .setIs24HourTime(DateTimeUtils.Is24HourTime)
                .setIndicatorColor(getResources().getColor(R.color.appThemeColor_2))
                .build()
                .show();
    }

    private void openToDateSelection() {
        new SlideDateTimePicker.Builder(requireActivity().getSupportFragmentManager())
                .setListener(new SlideDateTimeListener() {
                    @Override
                    public void onDateTimeSet(Date date) {

                        if (Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(date.getTime(), "ddMMyyyy")).equals(fromDateDay) || Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(date.getTime(), "ddMMyyyy")).after(fromDateDay)) {

                            toDateDay = Utils.convertStringToDate("ddMMyyyy", getDateFromMilliSec(date.getTime(), "ddMMyyyy"));

                            toSelectedTime = Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, date);

                            toDateEditBox.setText(getDateFromMilliSec(date.getTime(), "dd MMM yyyy"));

                            getPastOrders(false, fromSelectedTime, toSelectedTime);
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_TO_DATE_RESTRICT"));
                        }

                    }

                    @Override
                    public void onDateTimeCancel() {

                    }

                })
                .setTimePickerEnabled(false)
                .setInitialDate(new Date())
                .setMaxDate(new Date())
                .setIs24HourTime(DateTimeUtils.Is24HourTime)
                .setIndicatorColor(getResources().getColor(R.color.appThemeColor_2))
                .build()
                .show();
    }

    @Override
    public void onItemClickList(View view, int position) {
        Bundle bn = new Bundle();
        bn.putSerializable("iOrderId", listData.get(position).get("iOrderId"));
        new ActUtils(getActivity()).startActWithData(OrderDetailsActivity.class, bn);
    }

    @SuppressLint("SetTextI18n")
    private void getPastOrders(boolean isLoadMore, String fromSelectedTime, String toSelectedTime) {

        if (!isLoadMore) {
            listData.clear();
            orderHistoryRecycleAdapter.notifyDataSetChanged();
            isNextPageAvailable = false;
            mIsLoading = true;
            detailsArea.setVisibility(View.GONE);

            SkeletonView(false);
            SkeletonView(true);
        }

        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        noOrdersTxt.setVisibility(View.GONE);

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getOrderHistory");
        parameters.put("iGeneralUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("vFromDate", SELECTED_DATE);
        if (myBookingFragment != null) {
            parameters.put("vSubFilterParam", myBookingFragment.selOrderSubFilterType);
        } else {
            parameters.put("vSubFilterParam", bookingAct.selOrderSubFilterType);
        }

        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }
        parameters.put("eSystem", Utils.eSystem_Type);

        if (this.currentExeTask != null) {
            this.currentExeTask.cancel(true);
            this.currentExeTask = null;
        }
        this.currentExeTask = ApiHandler.execute(getActContext(), parameters, responseString -> {
            this.currentExeTask = null;
            SkeletonView(false);

            noOrdersTxt.setVisibility(View.GONE);
            swipeRefreshLayout.setRefreshing(false);

            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {
                detailsArea.setVisibility(View.VISIBLE);

                if (!isLoadMore) {
                    JSONArray noDataArr = generalFunc.getJsonArray("EARNING_DATA", responseStringObject);
                    if (calendarArrayList == null) {
                        calendarArrayList = new ArrayList<>();
                    }
                    if (noDataArr != null && noDataArr.length() > 0) {

                        for (int nod = 0; nod < noDataArr.length(); nod++) {
                            String[] dateParts = generalFunc.getJsonValue(noDataArr, nod).toString().split("-");
                            int selYear = Integer.parseInt(dateParts[0]);
                            int selMonth = Integer.parseInt(dateParts[1]);
                            int selDay = Integer.parseInt(dateParts[2]);

                            Calendar date = Calendar.getInstance();
                            date.set(selYear, (selMonth - 1), selDay);
                            calendarArrayList.add(date);
                        }
                    }
                }

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObject);
                    String TotalOrder = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TotalOrder", responseStringObject));
                    String TotalEarning = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TotalEarning", responseStringObject));
                    String AvgRating = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("AvgRating", responseStringObject));

                    earningFareVTxt.setText(TotalEarning.equals("") ? "--" : TotalEarning);
                    totalOrderVTxt.setText(TotalOrder.equals("") ? "--" : TotalOrder);
                    avgRatingCalcTxt.setText(AvgRating.equals("") ? "--" : AvgRating);
                    avgRatingCalcTxt.setText("" + GeneralFunctions.parseFloatValue(0, Utils.checkText(AvgRating) ? AvgRating : "0"));

                    JSONArray arr_orders = generalFunc.getJsonArray(Utils.message_str, responseStringObject);


                    if (arr_orders != null) {
                        for (int i = 0; i < arr_orders.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_orders, i);

                            JSONArray arr_date = generalFunc.getJsonArray(Utils.data_str, obj_temp);
                            for (int j = 0; j < arr_date.length(); j++) {
                                HashMap<String, String> map = new HashMap<>();

                                JSONObject obj_date_temp = generalFunc.getJsonObject(arr_date, j);

                                map.put("iOrderId", generalFunc.getJsonValueStr("iOrderId", obj_date_temp));
                                map.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", obj_date_temp));
                                map.put("vCompany", generalFunc.getJsonValueStr("vCompany", obj_date_temp));
                                map.put("vServiceCategoryName", generalFunc.getJsonValueStr("vServiceCategoryName", obj_date_temp));
                                map.put("vOrderNo", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vOrderNo", obj_date_temp)));
                                String tOrderRequestDate_Org = generalFunc.getJsonValueStr("tOrderRequestDate_Org", obj_date_temp);
                                map.put("tOrderRequestDate_Org", generalFunc.convertNumberWithRTL(tOrderRequestDate_Org));


                                /*try {
                                    map.put("ConvertedOrderRequestDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tOrderRequestDate_Org, Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                                    map.put("ConvertedOrderRequestTime", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(tOrderRequestDate_Org, Utils.OriginalDateFormate, DateTimeUtils.OriginalTimeFormat)));

                                } catch (Exception e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                    map.put("ConvertedOrderRequestDate", "");
                                    map.put("ConvertedOrderRequestTime", "");
                                }*/

                                map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_date_temp));
                                map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_date_temp));
                                map.put("tDisplayTimeAbbr", generalFunc.getJsonValueStr("tDisplayTimeAbbr", obj_date_temp));
                                map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_date_temp));

                                map.put("vAvgRating", "" + GeneralFunctions.parseFloatValue(0, generalFunc.getJsonValueStr("vAvgRating", obj_temp)));
                                map.put("tOrderRequestDate", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("tOrderRequestDate", obj_date_temp)));
                                map.put("UseName", generalFunc.getJsonValueStr("UseName", obj_date_temp));
                                map.put("vUserAddress", generalFunc.getJsonValueStr("vUserAddress", obj_date_temp));

                                map.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_date_temp));
                                map.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_date_temp));

                                map.put("TotalItems", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TotalItems", obj_date_temp)));
                                map.put("vImage", generalFunc.getJsonValueStr("vImage", obj_date_temp));
                                map.put("iStatus", generalFunc.getJsonValueStr("iStatus", obj_date_temp));
                                map.put("fTotalGenerateFare", generalFunc.convertNumberWithRTL((generalFunc.getJsonValueStr("fTotalGenerateFare", obj_date_temp))));
                                map.put("EarningFare", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("EarningFare", obj_date_temp)));
                                map.put("LBL_AMT_GENERATE_PENDING", generalFunc.retrieveLangLBl("", "LBL_AMT_GENERATE_PENDING"));
                                map.put("iStatusCode", generalFunc.getJsonValueStr("iStatusCode", obj_date_temp));
                                map.put("TYPE", "" + OrderHistoryRecycleAdapter.TYPE_ITEM);
                                int item = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValueStr("TotalItems", obj_date_temp));
                                map.put("LBL_ITEM", item <= 1 ? generalFunc.retrieveLangLBl("", "LBL_ITEM") : generalFunc.retrieveLangLBl("", "LBL_ITEMS"));

                                listData.add(map);
                            }

                        }

                    }
                    buildFilterTypes(responseStringObject);
                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }

                    orderHistoryRecycleAdapter.notifyDataSetChanged();

                } else {
                    buildFilterTypes(responseStringObject);

                    if (listData.size() == 0) {
                        removeNextPageConfig();
                        noOrdersTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        noOrdersTxt.setVisibility(View.VISIBLE);
                        earningFareVTxt.setText("--");
                        totalOrderVTxt.setText("--");
                        avgRatingCalcTxt.setText("--");
                    }
                }
            } else {
                if (!isLoadMore) {
                    buildFilterTypes(responseStringObject);
                    removeNextPageConfig();
                    generateErrorView();
                    earningFareVTxt.setText("--");
                    totalOrderVTxt.setText("--");
                    avgRatingCalcTxt.setText("--");
                }

            }
            mIsLoading = false;

        });

    }

    private void SkeletonView(boolean isShow) {
        if (myBookingFragment != null && myBookingFragment.checkCurrentFragment(2) || bookingAct != null && bookingAct.checkCurrentFragment(2)) {
            if (isShow) {
                //SkeletonViewHandler.getInstance().ShowNormalSkeletonView(view.findViewById(R.id.llMainArea), R.layout.skeleton_your_bookings);
                SkeletonViewHandler.getInstance().showListSkeletonView(historyRecyclerView, R.layout.skeleton_your_bookings, orderHistoryRecycleAdapter);
            } else {
                SkeletonViewHandler.getInstance().hideSkeletonView();
            }
        }
    }

    private void buildFilterTypes(JSONObject responseStringObject) {
        if (responseStringObject == null) return;
        String eFilterSel = generalFunc.getJsonValueStr("eFilterSel", responseStringObject);

        JSONArray subFilterOptionArr = generalFunc.getJsonArray("subFilterOption", responseStringObject);

        subFilterlist = new ArrayList<>();
        if (subFilterOptionArr != null && subFilterOptionArr.length() > 0) {
            for (int i = 0; i < subFilterOptionArr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(subFilterOptionArr, i);
                HashMap<String, String> map = new HashMap<>();
                String vTitle = generalFunc.getJsonValueStr("vTitle", obj_temp);
                map.put("vTitle", vTitle);
                String vSubFilterParam = generalFunc.getJsonValueStr("vSubFilterParam", obj_temp);
                map.put("vSubFilterParam", vSubFilterParam);

                if (vSubFilterParam.equalsIgnoreCase(eFilterSel)) {
                    filterTxt.setText(vTitle);
                    filterDropImg.setVisibility(View.VISIBLE);
                    if (myBookingFragment != null) {
                        myBookingFragment.selOrderSubFilterType = eFilterSel;
                        myBookingFragment.orderSubFilterPosition = i;

                    } else {
                        bookingAct.selOrderSubFilterType = eFilterSel;
                        bookingAct.orderSubFilterPosition = i;
                    }
                }
                calenderHeaderLayout.setVisibility(View.VISIBLE);
                subFilterlist.add(map);
            }
        }
        if (myBookingFragment != null) {
            myBookingFragment.subFilterManage(subFilterlist, "Order");

        } else {
            bookingAct.subFilterManage(subFilterlist, "Order");
        }
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        orderHistoryRecycleAdapter.removeFooterView();
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getPastOrders(false, fromSelectedTime, toSelectedTime));
    }

    private void openCalender(Calendar registrationDate) {
        if (calendarArrayList == null) {
            return;
        }
        Calendar[] days = calendarArrayList.toArray(new Calendar[0]);

        DatePicker.show(getActContext(), generalFunc, registrationDate, Calendar.getInstance(), SELECTED_DATE, days, (year, monthOfYear, dayOfMonth) -> {

            String datePicketSelectDate = year + "-" + monthOfYear + "-" + dayOfMonth;
            if (SELECTED_DATE.equalsIgnoreCase(datePicketSelectDate)) {
                return;
            }

            SimpleDateFormat date_format1 = new SimpleDateFormat(DateTimeUtils.DayFormatEN, Locale.US);
            try {
                Date cal = date_format1.parse(datePicketSelectDate);
                if (cal != null) {
                    SELECTED_DATE = date_format1.format(cal.getTime());
                    mDate = cal;
                    SimpleDateFormat date_format = new SimpleDateFormat(DateTimeUtils.DateFormat, MyUtils.getLocale());
                    dateTitle.setText(date_format.format(cal.getTime()));

                    removeNextPageConfig();
                    if (Utils.checkText(SELECTED_DATE)) {
                        getPastOrders(false, fromSelectedTime, toSelectedTime);
                    }
                }

            } catch (ParseException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }


        });
    }

    private static class setOnTouchList implements View.OnTouchListener {

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {
            if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view.hasFocus()) {
                view.performClick();
            }
            return true;
        }
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == fromDateEditBox.getId()) {
            openFromDateSelection();
        } else if (i == toDateEditBox.getId()) {
            openToDateSelection();
        } else if (i == filterArea.getId()) {
            if (myBookingFragment != null) {
                myBookingFragment.BuildType("Order");
            } else {
                bookingAct.BuildType("Order");
            }
        }
    }


    @Override
    public void onDestroyView() {
        super.onDestroyView();
        Utils.hideKeyboard(requireActivity());
    }

    @Override
    public void onRefresh() {
        swipeRefreshLayout.setRefreshing(true);
        getPastOrders(false, fromSelectedTime, toSelectedTime);
    }
}