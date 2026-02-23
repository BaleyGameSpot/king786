package com.fragments;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.text.format.Time;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.act.BiddingHistoryDetailActivity;
import com.act.BiddingViewDetailsActivity;
import com.act.BookingsActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.deliverAll.LiveTaskListActivity;
import com.adapter.files.BiddingListRecycleAdapter;
import com.general.DatePicker;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;

public class BiddingBookingFragment extends BaseFragment implements BiddingListRecycleAdapter.OnItemClickListener, SwipeRefreshLayout.OnRefreshListener {

    private GeneralFunctions generalFunc;
    private JSONObject userProfileJsonObj;
    boolean mIsLoading = false;
    public boolean isNextPageAvailable = false;

    private MTextView noRidesTxt;
    public String next_page_str = "";
    private ErrorView errorView;

    private RecyclerView biddingRecyclerView;
    private BiddingListRecycleAdapter biddingListRecycleAdapter;

    private String SELECTED_DATE = "";
    private View view;
    private SwipeRefreshLayout swipeRefreshLayout;

    public MTextView filterTxt;
    ArrayList<HashMap<String, String>> subFilterlist = new ArrayList<>();
    ArrayList<HashMap<String, String>> listData = new ArrayList<>();

    private MyBookingFragment myBookingFragment = null;
    private BookingsActivity bookingAct;
    private HashMap<String, String> earningamtmap = new HashMap<>();
    private ImageView filterDropImg;

    public LinearLayout calenderHeaderLayout, titleContainerView;
    private ArrayList<Calendar> calendarArrayList;
    private MTextView dateTitle;
    private Date mDate;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        view = inflater.inflate(R.layout.fragment_biding, container, false);

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

        LinearLayout filterArea = view.findViewById(R.id.filterArea);
        addToClickHandler(filterArea);

        swipeRefreshLayout = view.findViewById(R.id.swipe_refresh_layout);
        swipeRefreshLayout.setOnRefreshListener(this);

        biddingRecyclerView = view.findViewById(R.id.biddingRecyclerView);
        noRidesTxt = view.findViewById(R.id.noRidesTxt);
        filterTxt = view.findViewById(R.id.filterTxt);
        errorView = view.findViewById(R.id.errorView);

        biddingListRecycleAdapter = new BiddingListRecycleAdapter(getActContext(), listData, generalFunc, false);
        biddingRecyclerView.setAdapter(biddingListRecycleAdapter);
        biddingListRecycleAdapter.setOnItemClickListener(this);

        biddingRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                int topRowVerticalPosition = (recyclerView == null || recyclerView.getChildCount() == 0) ? 0 : recyclerView.getChildAt(0).getTop();
                swipeRefreshLayout.setEnabled(topRowVerticalPosition >= 0);

                int visibleItemCount = biddingRecyclerView.getLayoutManager().getChildCount();
                int totalItemCount = biddingRecyclerView.getLayoutManager().getItemCount();
                int firstVisibleItemPosition = ((LinearLayoutManager) biddingRecyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                    mIsLoading = true;
                    biddingListRecycleAdapter.addFooterView();
                    getBiddingPosts(true);
                } else if (!isNextPageAvailable) {
                    biddingListRecycleAdapter.removeFooterView();
                }
            }

            @Override
            public void onScrollStateChanged(RecyclerView recyclerView, int newState) {
                super.onScrollStateChanged(recyclerView, newState);
            }
        });

        long fromDateMillis = System.currentTimeMillis() - (7 * 24 * 60 * 60 * 1000);
        Time fromDate = new Time();
        fromDate.set(fromDateMillis);

        long toDateMillis = System.currentTimeMillis();
        Time toDate = new Time();
        toDate.set(toDateMillis);

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
            getBiddingPosts(false);
        }
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
            if (bookingAct != null && bookingAct.filterImageview != null) {
                bookingAct.filterImageview.setVisibility(View.GONE);
            }
        }

        if (myBookingFragment != null && myBookingFragment.checkCurrentFragment(3) || bookingAct != null && bookingAct.checkCurrentFragment(3)) {
            getBiddingPosts(false);
        }
    }

    @SuppressLint("SetTextI18n")
    private void getBiddingPosts(boolean isLoadMore) {
        if (!isLoadMore) {
            listData.clear();
            biddingListRecycleAdapter.notifyDataSetChanged();
            isNextPageAvailable = false;
            mIsLoading = true;
            SkeletonView(false);
            SkeletonView(true);
        }

        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }
        noRidesTxt.setVisibility(View.GONE);

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getBiddingPosts");
        parameters.put("memberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("vFromDate", SELECTED_DATE);
        if (myBookingFragment != null) {
            parameters.put("vSubFilterParam", myBookingFragment.biddingSelSubFilterType);
        } else {
            parameters.put("vSubFilterParam", bookingAct.biddingSelSubFilterType);
        }
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            noRidesTxt.setVisibility(View.GONE);
            swipeRefreshLayout.setRefreshing(false);

            if (!isLoadMore) {
                listData.clear();
            }

            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null && !responseStringObject.toString().equals("")) {

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

                if (listData.size() == 0) {
                    earningamtmap.put("header", "true");
                    earningamtmap.put("TripCount", "--");
                    earningamtmap.put("TotalEarning", "--");
                    earningamtmap.put("AvgRating", "--");
                    listData.add(earningamtmap);
                }

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {

                    if (!isLoadMore) {
                        listData.clear();
                        earningamtmap.put("header", "true");
                        earningamtmap.put("TripCount", "--");
                        earningamtmap.put("TotalEarning", "--");
                        earningamtmap.put("AvgRating", "--");
                        listData.add(earningamtmap);
                    }

                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObject);
                    String currencySymbol = generalFunc.getJsonValueStr("CurrencySymbol", responseStringObject);

                    JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseStringObject);
                    if (arr_rides != null) {
                        int arrRidesSize = arr_rides.length();

                        if (arr_rides.length() > 0) {

                            for (int i = 0; i < arrRidesSize; i++) {
                                JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);
                                HashMap<String, String> map = new HashMap<>();

                                map.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
                                map.put("tDescription", generalFunc.getJsonValueStr("tDescription", obj_temp));
                                map.put("fBiddingAmount", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fBiddingAmount", obj_temp)));
                                map.put("totalEarning", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("totalEarning", obj_temp)));
                                map.put("iBiddingId", generalFunc.getJsonValueStr("iBiddingId", obj_temp));
                                map.put("vBiddingPostNo", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vBiddingPostNo", obj_temp)));
                                map.put("eStatus", generalFunc.getJsonValueStr("eStatus", obj_temp));
                                map.put("iBiddingPostId", generalFunc.getJsonValueStr("iBiddingPostId", obj_temp));
                                map.put("dBiddingDate", generalFunc.getJsonValueStr("dBiddingDate", obj_temp));
                                map.put("vServiceAddress", generalFunc.getJsonValueStr("vServiceAddress", obj_temp));
                                map.put("bidding_status", generalFunc.getJsonValueStr("bidding_status", obj_temp));

                                map.put("showDetailBtn", generalFunc.getJsonValueStr("showDetailBtn", obj_temp));
                                map.put("biddingDetails", generalFunc.getJsonValueStr("biddingDetails", obj_temp));
                                map.put("vStatus_BG_color", generalFunc.getJsonValueStr("vStatus_BG_color", obj_temp));
                                map.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));
                                map.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
                                /*try {
                                    String dBiddingDate = generalFunc.getJsonValueStr("dBiddingDate", obj_temp);
                                    map.put("ConvertedTripRequestDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBiddingDate, Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                                    map.put("ConvertedTripRequestTime", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBiddingDate, Utils.OriginalDateFormate, DateTimeUtils.OriginalTimeFormat)));
                                } catch (Exception e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                    map.put("ConvertedTripRequestDate", "");
                                    map.put("ConvertedTripRequestTime", "");
                                }*/
                                map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                                map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_temp));
                                map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));
                                listData.add(map);
                            }
                        }
                    }

                    listData.get(0).put("TripCount", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TripCount", responseStringObject)));
                    String TotalEarning = generalFunc.getJsonValueStr("TotalEarning", responseStringObject);
                    String TotalEarningAmount = generalFunc.getJsonValueStr("TotalEarningAmount", responseStringObject);
                    listData.get(0).put("TotalEarning", Utils.checkText(TotalEarningAmount) ? generalFunc.convertNumberWithRTL(TotalEarningAmount) : (currencySymbol + generalFunc.convertNumberWithRTL(TotalEarning)));
                    listData.get(0).put("AvgRating", "" + GeneralFunctions.parseFloatValue(0, generalFunc.getJsonValueStr("AvgRating", responseStringObject)));


                    buildFilterTypes(responseStringObject);
                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }
                } else {
                    buildFilterTypes(responseStringObject);

                    if (listData.size() == 1) {
                        removeNextPageConfig();
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        noRidesTxt.setVisibility(View.VISIBLE);

                        listData.get(0).put("TripCount", "--");
                        listData.get(0).put("TotalEarning", "--");
                        listData.get(0).put("AvgRating", "--");
                    }
                }
            } else {
                if (!isLoadMore) {
                    buildFilterTypes(responseStringObject);
                    removeNextPageConfig();
                    generateErrorView();

                    if (listData.size() == 1) {
                        removeNextPageConfig();
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        noRidesTxt.setVisibility(View.VISIBLE);

                        listData.get(0).put("TripCount", "--");
                        listData.get(0).put("TotalEarning", "--");
                        listData.get(0).put("AvgRating", "--");
                    }
                }
            }

            biddingListRecycleAdapter.notifyDataSetChanged();
            mIsLoading = false;
            SkeletonView(false);
        });

    }

    private void SkeletonView(boolean isShow) {
        if (myBookingFragment != null && myBookingFragment.checkCurrentFragment(3) || bookingAct != null && bookingAct.checkCurrentFragment(3)) {
            if (isShow) {
                //SkeletonViewHandler.getInstance().ShowNormalSkeletonView(view.findViewById(R.id.llMainArea), R.layout.skeleton_your_bookings);
                SkeletonViewHandler.getInstance().showListSkeletonView(biddingRecyclerView, R.layout.skeleton_your_bookings, biddingListRecycleAdapter);
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
                        myBookingFragment.biddingSelSubFilterType = eFilterSel;
                        myBookingFragment.biddingSubFilterPosition = i;
                    } else {
                        bookingAct.biddingSelSubFilterType = eFilterSel;
                        bookingAct.biddingSubFilterPosition = i;
                    }
                }
                if (generalFunc.getJsonValueStr("eDisplayEarningSection", responseStringObject).equalsIgnoreCase("Yes")) {
                    calenderHeaderLayout.setVisibility(View.VISIBLE);
                    listData.get(0).put("isCalenderView", "yes");
                } else {
                    calenderHeaderLayout.setVisibility(View.GONE);
                    listData.get(0).put("isCalenderView", "no");
                }
                subFilterlist.add(map);
            }
        }
        if (myBookingFragment != null) {
            myBookingFragment.subFilterManage(subFilterlist, "Bidding");
        } else {
            bookingAct.subFilterManage(subFilterlist, "Bidding");
        }
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
                        getBiddingPosts(false);
                    }
                }

            } catch (ParseException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }


        });
    }

    @Override
    public void onItemClickList(View v, int position, String type) {
        Utils.hideKeyboard(requireActivity());
        HashMap<String, String> listData1 = listData.get(position);
        if (type.equalsIgnoreCase("ViewDetail")) {
            Bundle bn = new Bundle();
            bn.putString("iBiddingPostId", listData1.get("iBiddingPostId"));

            if (Objects.requireNonNull(listData1.get("eStatus")).equalsIgnoreCase("Completed")) {
                new ActUtils(getActContext()).startActWithData(BiddingHistoryDetailActivity.class, bn);
            } else {
                new ActUtils(getActContext()).startActWithData(BiddingViewDetailsActivity.class, bn);
            }

        }
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == R.id.filterArea) {
            if (myBookingFragment != null) {
                myBookingFragment.BuildType("Bidding");
            } else {
                bookingAct.BuildType("Bidding");
            }
        }
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        biddingListRecycleAdapter.removeFooterView();
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getBiddingPosts(false));
    }

    private Context getActContext() {
        if (myBookingFragment != null) {
            return myBookingFragment.getActContext();
        } else {
            return bookingAct.getActContext();
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
        getBiddingPosts(false);
    }
}