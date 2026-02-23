package com.fragments;

import android.annotation.SuppressLint;
import android.content.Context;
import android.location.Location;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.act.BookingsActivity;
import com.act.ContactUsActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.MoreServiceInfoActivity;
import com.act.MyWalletActivity;
import com.act.RideHistoryDetailActivity;
import com.act.deliverAll.LiveTaskListActivity;
import com.adapter.files.MyHistoryRecycleAdapter;
import com.dialogs.OpenListView;
import com.general.DatePicker;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
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
public class HistoryFragment extends BaseFragment implements MyHistoryRecycleAdapter.OnItemClickListener, SwipeRefreshLayout.OnRefreshListener {

    GeneralFunctions generalFunc;
    JSONObject userProfileJsonObj;
    View view;
    MTextView noRidesTxt;
    public MTextView filterTxt;
    RecyclerView myBookingsRecyclerView;
    ErrorView errorView;
    ImageView filterDropImg;

    MyHistoryRecycleAdapter myBookingsRecyclerAdapter;
    ArrayList<HashMap<String, String>> list;

    boolean mIsLoading = false;
    public boolean isNextPageAvailable = false;

    String SELECTED_DATE = "", selectedItemId = "", tripdataPage = "", titleDailog = "";
    public String next_page_str = "";

    BookingsActivity bookingAct = null;
    ArrayList<HashMap<String, String>> filterlist;
    ArrayList<HashMap<String, String>> subFilterlist;
    AlertDialog dialog_declineOrder;

    int HISTORYDETAILS = 1, selCurrentPosition = -1;

    private SwipeRefreshLayout swipeRefreshLayout;
    HistoryFragment historyFragment;
    private MyBookingFragment myBookingFragment = null;
    HashMap<String, String> earningamtmap = new HashMap<>();

    public LinearLayout calenderHeaderLayout, titleContainerView;
    private ArrayList<Calendar> calendarArrayList;
    private MTextView dateTitle;
    private Date mDate;

    @SuppressLint("ClickableViewAccessibility")
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        view = inflater.inflate(R.layout.fragment_history, container, false);

        if (getActivity() instanceof MainActivity) {
            myBookingFragment = ((MainActivity) getActivity()).myBookingFragment;
            historyFragment = myBookingFragment.getHistoryFrag();
        } else if (getActivity() instanceof MainActivity_22) {
            myBookingFragment = ((MainActivity_22) getActivity()).myBookingFragment;
            historyFragment = myBookingFragment.getHistoryFrag();
        } else if (getActivity() instanceof LiveTaskListActivity) {
            myBookingFragment = ((LiveTaskListActivity) getActivity()).myBookingFragment;
            historyFragment = myBookingFragment.getHistoryFrag();
        } else if (getActivity() instanceof BookingsActivity) {
            myBookingFragment = null;
            bookingAct = (BookingsActivity) getActivity();
            historyFragment = bookingAct.getHistoryFrag();
        }

        generalFunc = MyApp.getInstance().getGeneralFun(requireActivity());

        userProfileJsonObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        filterDropImg = view.findViewById(R.id.filterDropImg);
        filterDropImg.setVisibility(View.GONE);

        addCalenderView();

        noRidesTxt = view.findViewById(R.id.noRidesTxt);
        filterTxt = view.findViewById(R.id.filterTxt);
        myBookingsRecyclerView = view.findViewById(R.id.myBookingsRecyclerView);
        errorView = view.findViewById(R.id.errorView);

        swipeRefreshLayout = view.findViewById(R.id.swipe_refresh_layout);
        swipeRefreshLayout.setOnRefreshListener(this);

        LinearLayout filterArea = view.findViewById(R.id.filterArea);
        filterArea.setOnClickListener(new setOnClickList());

        list = new ArrayList<>();

        myBookingsRecyclerAdapter = new MyHistoryRecycleAdapter(getActContext(), list, generalFunc, false);
        myBookingsRecyclerView.setAdapter(myBookingsRecyclerAdapter);
        myBookingsRecyclerAdapter.setOnItemClickListener(this);

        myBookingsRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                int topRowVerticalPosition = (recyclerView == null || recyclerView.getChildCount() == 0) ? 0 : recyclerView.getChildAt(0).getTop();
                swipeRefreshLayout.setEnabled(topRowVerticalPosition >= 0);

                int visibleItemCount = myBookingsRecyclerView.getLayoutManager().getChildCount();
                int totalItemCount = myBookingsRecyclerView.getLayoutManager().getItemCount();
                int firstVisibleItemPosition = ((LinearLayoutManager) myBookingsRecyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                    mIsLoading = true;
                    myBookingsRecyclerAdapter.addFooterView();
                    getBookingsHistory(true);
                } else if (!isNextPageAvailable) {
                    myBookingsRecyclerAdapter.removeFooterView();
                }
            }

            @Override
            public void onScrollStateChanged(RecyclerView recyclerView, int newState) {
                super.onScrollStateChanged(recyclerView, newState);
            }
        });

        if (ServiceModule.IsTrackingProvider) {
            filterArea.setVisibility(View.GONE);
            calenderHeaderLayout.setVisibility(View.VISIBLE);
        }
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

        SimpleDateFormat date_format = new SimpleDateFormat(DateTimeUtils.DayFormatEN, MyUtils.getLocale());
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
            getBookingsHistory(false);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (Utils.checkText(SELECTED_DATE) && myBookingFragment != null && myBookingFragment.checkCurrentFragment(1) || bookingAct != null && bookingAct.checkCurrentFragment(1)) {
            getBookingsHistory(false);
        }
    }

    private boolean isDeliver(String eType) {
        return eType.equals(Utils.CabGeneralType_Deliver) || eType.equalsIgnoreCase("Deliver");
    }

    @Override
    public void onCancelBookingClickList(View v, int position) {
        confirmCancelBooking(list.get(position).get("iCabBookingId"), list.get(position));
    }

    @Override
    public void onTripStartClickList(View v, int position) {
        String contentMsg = "";

        String eTypeVal = list.get(position).get("eTypeVal");

        if (eTypeVal.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            contentMsg = generalFunc.retrieveLangLBl("", "LBL_CONFIRM_START_JOB");
        } else if (eTypeVal.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            contentMsg = generalFunc.retrieveLangLBl("", "LBL_CONFIRM_START_TRIP_TXT");
        } else {
            contentMsg = generalFunc.retrieveLangLBl("", "LBL_CONFIRM_START_DELIVERY");
        }
        buildMsgOnStartTripBtn(list.get(position).get("iCabBookingId"), list.get(position).get("iActive"), contentMsg, eTypeVal);
    }

    @Override
    public void onViewServiceClickList(View v, int position) {
        Bundle bundle = new Bundle();
        bundle.putString("iCabBookingId", list.get(position).get("iCabBookingId"));
        new ActUtils(getActContext()).startActWithData(MoreServiceInfoActivity.class, bundle);
    }

    @Override
    public void onDetailViewClickList(View v, int position) {
        Bundle bn = new Bundle();
        bn.putString("iTripId", list.get(position).get("iTripId"));
        new ActUtils(getActivity()).startActForResult(RideHistoryDetailActivity.class, bn, HISTORYDETAILS);
    }

    private void confirmCancelBooking(final String iCabBookingId, HashMap<String, String> list) {
        if (myBookingFragment != null && myBookingFragment.selSubFilterType != null & myBookingFragment.selSubFilterType.equalsIgnoreCase("pending") && list.get("eType").equalsIgnoreCase("Ride") && generalFunc.getJsonValueStr("ENABLE_SCHEDULED_RIDE_FLOW", userProfileJsonObj).equalsIgnoreCase("Yes") && list.get("vBookingType").equalsIgnoreCase("schedule")) {
            generateCancelBookingDialog(iCabBookingId, list);

        } else if (bookingAct != null && bookingAct.selSubFilterType != null & bookingAct.selSubFilterType.equalsIgnoreCase("pending") && list.get("eType").equalsIgnoreCase("Ride") && generalFunc.getJsonValueStr("ENABLE_SCHEDULED_RIDE_FLOW", userProfileJsonObj).equalsIgnoreCase("Yes") && list.get("vBookingType").equalsIgnoreCase("schedule")) {
            generateCancelBookingDialog(iCabBookingId, list);
        } else {
            showDeclineReasonsAlert(iCabBookingId, list);
        }
    }

    private void generateCancelBookingDialog(String iCabBookingId, HashMap<String, String> list) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 1) {
                declineBooking(iCabBookingId, "", "", list);
            }
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_DECLINE_RIDE_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
        generateAlert.showAlertBox();
    }

    private void showDeclineReasonsAlert(String iCabBookingId, HashMap<String, String> listdata) {
        selCurrentPosition = -1;
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        if (listdata.get("iActive").equalsIgnoreCase("Pending")) {
            titleDailog = (generalFunc.retrieveLangLBl("Decline Job", listdata.get("eTypeVal").equalsIgnoreCase(Utils.CabGeneralType_Ride) ? "LBL_RIDE_LATER_DECLINE_TRIP_TEXT" : "LBL_DECLINE_BOOKING"));
        } else {
            if (listdata.get("eTypeVal").equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
                titleDailog = (generalFunc.retrieveLangLBl("Cancel Booking", "LBL_CANCEL_TRIP"));
            } else if (listdata.get("eTypeVal").equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                titleDailog = (generalFunc.retrieveLangLBl("", "LBL_CANCEL_JOB"));
            } else {
                titleDailog = (generalFunc.retrieveLangLBl("", "LBL_CANCEL_DELIVERY"));
            }
        }

        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            reasonBox.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));

        ArrayList<HashMap<String, String>> sub_list = new ArrayList<>();

        MTextView cancelTxt = dialogView.findViewById(R.id.cancelTxt);
        MTextView submitTxt = dialogView.findViewById(R.id.submitTxt);
        MTextView subTitleTxt = dialogView.findViewById(R.id.subTitleTxt);
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);
        subTitleTxt.setText(titleDailog);
        MTextView declinereasonBox = dialogView.findViewById(R.id.declinereasonBox);
        declinereasonBox.setText("-- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_CANCEL_REASON") + " --");
        submitTxt.setClickable(false);
        submitTxt.setEnabled(false);
        submitTxt.setTextColor(getResources().getColor(R.color.gray_holo_light));
        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));

        submitTxt.setOnClickListener(v -> {
            selectedItemId = sub_list.get(selCurrentPosition).get("id");

            if (selCurrentPosition == -1) {
                return;
            }

            if (!Utils.checkText(reasonBox) && selCurrentPosition == (sub_list.size() - 1)) {
                errorTextView.setVisibility(View.VISIBLE);
                errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                return;
            }

            if (listdata.get("iActive").equalsIgnoreCase("Pending")) {
                declineBooking(sub_list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox), reasonBox.getText().toString().trim(), listdata);
            } else {
                cancelTrip(sub_list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox), reasonBox.getText().toString().trim(), listdata);
            }

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
            parameters.put("eJobType", listdata.get("eJobType"));

            parameters.put("iCabBookingId", iCabBookingId);

            ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
                JSONObject responseStringObj = generalFunc.getJsonObject(responseString);
                sub_list.clear();
                if (Utils.checkText(responseString)) {
                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
                        if (arr_msg != null) {

                            for (int i = 0; i < arr_msg.length(); i++) {
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

                            OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_REASON"), sub_list, OpenListView.OpenDirection.CENTER, true, position -> {
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
                                submitTxt.setEnabled(true);
                                submitTxt.setTextColor(getResources().getColor(R.color.white));
                            }).show(selCurrentPosition, "title");
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
                        }
                    } else {
                        String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);
                        if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY) || message.equals("LBL_SERVER_COMM_ERROR")) {

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
        dialog_declineOrder.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        dialog_declineOrder.show();
    }

    private void acceptBooking(String iCabBookingId, String eConfirmByProvider) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateBookingStatus");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("iCabBookingId", iCabBookingId);
        parameters.put("eStatus", "Accepted");
        parameters.put("eConfirmByProvider", eConfirmByProvider);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (responseStringObj != null) {
                String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    list.clear();
                    myBookingsRecyclerAdapter.notifyDataSetChanged();
                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        if (myBookingFragment != null) {
                            myBookingFragment.selSubFilterType = "";
                        } else {
                            bookingAct.selSubFilterType = "";
                        }
                        getBookingsHistory(false);
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", message));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                } else {
                    String IS_LOW_WALLET_BALANCE = generalFunc.getJsonValueStr("IS_LOW_WALLET_BALANCE", responseStringObj);
                    if (IS_LOW_WALLET_BALANCE != null && IS_LOW_WALLET_BALANCE.equalsIgnoreCase("Yes")) {
                        MyUtils.buildLowBalanceMessage(getActContext(), generalFunc, userProfileJsonObj, generalFunc.retrieveLangLBl("", message), () -> {
                            if (generalFunc.getJsonValueStr("APP_PAYMENT_MODE", userProfileJsonObj).equalsIgnoreCase("Cash")) {
                                new ActUtils(getActContext()).startAct(ContactUsActivity.class);

                            } else {
                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            }
                        });
                        return;
                    }

                    String BookingFound = generalFunc.getJsonValueStr("BookingFound", responseStringObj);
                    if (BookingFound.equalsIgnoreCase("Yes")) {

                        GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                        alertBox.setCancelable(false);
                        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", message));
                        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                        alertBox.setBtnClickList(btn_id -> {
                            if (btn_id == 0) {
                                alertBox.closeAlertBox();
                            } else if (btn_id == 1) {
                                acceptBooking(iCabBookingId, "Yes");
                                alertBox.closeAlertBox();
                            }
                        });
                        alertBox.showAlertBox();
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void declineBooking(String iCancelReasonId, String comment, String reason, HashMap<String, String> data_trip) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateBookingStatus");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iCabBookingId", data_trip.get("iCabBookingId"));
        parameters.put("vCancelReason", reason);
        parameters.put("eStatus", "Declined");
        parameters.put("iCancelReasonId", iCancelReasonId);
        parameters.put("Reason", reason);
        parameters.put("Comment", comment);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);
            if (responseStringObj != null) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    list.clear();
                    myBookingsRecyclerAdapter.notifyDataSetChanged();

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        getBookingsHistory(false);
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void cancelTrip(String iCancelReasonId, String comment, String reason, HashMap<String, String> data_trip) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "cancelBooking");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iCabBookingId", data_trip.get("iCabBookingId"));
        parameters.put("Comment", comment);
        parameters.put("iCancelReasonId", iCancelReasonId);
        parameters.put("Reason", reason);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (responseStringObj != null) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    list.clear();
                    myBookingsRecyclerAdapter.notifyDataSetChanged();
                    getBookingsHistory(false);
                }
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
            } else {
                generalFunc.showError();
            }
        });

    }

    private void buildMsgOnStartTripBtn(final String iCabBookingId, String type, String contentMsg, String eTypeVal) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else {
                if (type.equalsIgnoreCase("Pending")) {
                    acceptBooking(iCabBookingId, "No");
                } else {
                    startTrip(iCabBookingId);
                }
            }
        });
        if (type.equalsIgnoreCase("Pending")) {
            generateAlert.setContentMessage("", eTypeVal.equalsIgnoreCase(Utils.CabGeneralType_Ride) ? generalFunc.retrieveLangLBl("", "LBL_RIDE_LATER_CONFIRM_ACCEPT_RIDE") : generalFunc.retrieveLangLBl("Are you sure? You want to accept this job.", "LBL_CONFIRM_ACCEPT_JOB"));
        } else {
            generateAlert.setContentMessage("", contentMsg);
        }
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
        generateAlert.showAlertBox();
    }

    private void startTrip(String iCabBookingId) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GenerateTrip");
        parameters.put("UserType", Utils.app_type);
        parameters.put("DriverID", generalFunc.getMemberId());
        parameters.put("iCabBookingId", iCabBookingId);
        parameters.put("GoogleServerKey", generalFunc.retrieveValue(Utils.GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY));


        if (myBookingFragment != null && myBookingFragment.userLocation != null) {
            parameters.put("vLatitude", "" + myBookingFragment.userLocation.getLatitude());
            parameters.put("vLongitude", "" + myBookingFragment.userLocation.getLongitude());
        } else if (bookingAct != null && bookingAct.userLocation != null) {
            parameters.put("vLatitude", "" + bookingAct.userLocation.getLatitude());
            parameters.put("vLongitude", "" + bookingAct.userLocation.getLongitude());
        } else if (GetLocationUpdates.getInstance() != null && GetLocationUpdates.getInstance().getLastLocation() != null) {
            Location lastLocation = GetLocationUpdates.getInstance().getLastLocation();
            parameters.put("vLatitude", "" + lastLocation.getLatitude());
            parameters.put("vLongitude", "" + lastLocation.getLongitude());
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (responseStringObj != null) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {

                    if (myBookingFragment != null) {
                        myBookingFragment.stopLocUpdates();
                    } else if (bookingAct != null && bookingAct.userLocation != null) {
                        bookingAct.stopLocUpdates();
                    }
                    //   MyApp.getInstance().restartWithGetDataApp();
                    MyApp.getInstance().refreshView(MyApp.getInstance().getCurrentAct(), responseString);
                } else {
                    String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);

                    if (message.equalsIgnoreCase("DO_RESTART")) {
                        MyApp.getInstance().restartWithGetDataApp();
                        return;
                    }
                    if (generalFunc.getJsonValueStr("DO_RELOAD", responseStringObj).equalsIgnoreCase("YES")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message), generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"), "", buttonId -> {
                            list.clear();
                            myBookingsRecyclerAdapter.notifyDataSetChanged();
                            getBookingsHistory(false);
                        });
                        return;
                    }
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void getBookingsHistory(final boolean isLoadMore) {
        if (errorView != null) {
            if (errorView.getVisibility() == View.VISIBLE) {
                errorView.setVisibility(View.GONE);
            }
        }
        if (!isLoadMore) {
            removeNextPageConfig();
            SkeletonView(false);
            SkeletonView(true);
        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getMemberBookings");
        parameters.put("memberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        if (myBookingFragment != null) {
            parameters.put("vFilterParam", myBookingFragment.selFilterType);
            String eFilterSel = myBookingFragment.selSubFilterType;
            parameters.put("vSubFilterParam", eFilterSel);
            parameters.put("dDateOrig", eFilterSel.equalsIgnoreCase("past") ? SELECTED_DATE : "");
        } else {
            parameters.put("vFilterParam", bookingAct.selFilterType);
            String eFilterSel = bookingAct.selSubFilterType;
            parameters.put("vSubFilterParam", eFilterSel);
            parameters.put("dDateOrig", eFilterSel.equalsIgnoreCase("past") ? SELECTED_DATE : "");
        }

        if (isLoadMore) {
            parameters.put("page", next_page_str);
        } else {
            earningamtmap.put("header", "true");
            earningamtmap.put("TripCount", "--");
            earningamtmap.put("TotalEarning", "--");
            earningamtmap.put("AvgRating", "--");
            list.add(earningamtmap);
            list.clear();
        }
        parameters.put("tripdataPage", tripdataPage);

        noRidesTxt.setVisibility(View.GONE);


        if (ServiceModule.IsTrackingProvider) {
            parameters.clear();
            parameters.put("type", "getTrackingTripsDriver");
            parameters.put("memberId", generalFunc.getMemberId());
            parameters.put("UserType", Utils.app_type);
            parameters.put("vFromDate", SELECTED_DATE);
            if (isLoadMore) {
                parameters.put("page", next_page_str);
            }
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {


            swipeRefreshLayout.setRefreshing(false);

            noRidesTxt.setVisibility(View.GONE);

            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            tripdataPage = generalFunc.getJsonValueStr("tripdataPage", responseObj);

            if (responseObj != null) {
                MyUtils.setPendingBookingsCount(generalFunc.getJsonValueStr("PendingRideRequestCount", responseObj));
                if (!isLoadMore) {
                    JSONArray noDataArr = generalFunc.getJsonArray("EARNING_DATA", responseObj);
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

                if (list.size() == 0) {
                    earningamtmap.put("header", "true");
                    earningamtmap.put("TripCount", "--");
                    earningamtmap.put("TotalEarning", "--");
                    earningamtmap.put("AvgRating", "--");
                    list.add(earningamtmap);
                }
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    if (!isLoadMore) {
                        list.clear();
                        earningamtmap.put("header", "true");
                        earningamtmap.put("TripCount", "--");
                        earningamtmap.put("TotalEarning", "--");
                        earningamtmap.put("AvgRating", "--");
                        list.add(earningamtmap);
                    }

                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseObj);
                    String currencySymbol = generalFunc.getJsonValueStr("CurrencySymbol", responseObj);

                    JSONArray arr_rides = generalFunc.getJsonArray(Utils.message_str, responseObj);

                    if (arr_rides != null && arr_rides.length() > 0) {
                        String LBL_JOB_LOCATION_TXT = "", LBL_RENTAL_CATEGORY_TXT = "", LBL_INTERCITY_RETURN_LOCATION = "";
                        String LBL_DELIVERY_NO = "", LBL_CANCEL_BOOKING = "", LBL_BOOKING = "", LBL_PICK_UP_LOCATION = "", LBL_DEST_LOCATION = "", LBL_SENDER_LOCATION = "", LBL_RECEIVER_LOCATION = "";
                        String LBL_Status = "", LBL_VIEW_REASON = "", LBL_REBOOKING = "", LBL_RESCHEDULE = "", LBL_VIEW_REQUESTED_SERVICES = "";
                        String LBL_MULTI_LIVE_TRACK_TEXT = "", LBL_VIEW_DETAILS = "";
                        String LBL_ACCEPT_JOB = "", LBL_START_TRIP = "", LBL_DECLINE_JOB = "", LBL_CANCEL_TRIP = "", LBL_START_JOB = "", LBL_CANCEL_JOB = "", LBL_ACCEPT = "", LBL_DECLINE = "";
                        if (arr_rides.length() > 0) {
                            LBL_Status = generalFunc.retrieveLangLBl("", "LBL_Status");
                            LBL_RENTAL_CATEGORY_TXT = generalFunc.retrieveLangLBl("", "LBL_RENTAL_CATEGORY_TXT");
                            LBL_DELIVERY_NO = generalFunc.retrieveLangLBl("Delivery No", "LBL_DELIVERY_NO");
                            LBL_CANCEL_BOOKING = generalFunc.retrieveLangLBl("", "LBL_CANCEL_BOOKING");
                            LBL_BOOKING = generalFunc.retrieveLangLBl("", "LBL_BOOKING_TXT");

                            LBL_PICK_UP_LOCATION = generalFunc.retrieveLangLBl("", "LBL_PICK_UP_LOCATION");
                            LBL_DEST_LOCATION = generalFunc.retrieveLangLBl("", "LBL_DEST_LOCATION");
                            LBL_JOB_LOCATION_TXT = generalFunc.retrieveLangLBl("", "LBL_JOB_LOCATION_TXT");
                            LBL_SENDER_LOCATION = generalFunc.retrieveLangLBl("", "LBL_SENDER_LOCATION");
                            LBL_RECEIVER_LOCATION = generalFunc.retrieveLangLBl("", "LBL_RECEIVER_LOCATION");

                            LBL_MULTI_LIVE_TRACK_TEXT = generalFunc.retrieveLangLBl("", "LBL_MULTI_LIVE_TRACK_TEXT");
                            LBL_VIEW_DETAILS = generalFunc.retrieveLangLBl("", "LBL_VIEW_DETAILS");
                            LBL_VIEW_REASON = generalFunc.retrieveLangLBl("", "LBL_VIEW_REASON");
                            LBL_RESCHEDULE = generalFunc.retrieveLangLBl("", "LBL_RESCHEDULE");
                            LBL_REBOOKING = generalFunc.retrieveLangLBl("", "LBL_REBOOKING");
                            LBL_VIEW_REQUESTED_SERVICES = generalFunc.retrieveLangLBl("", "LBL_VIEW_REQUESTED_SERVICES");

                            LBL_ACCEPT_JOB = generalFunc.retrieveLangLBl("", "LBL_ACCEPT_JOB");
                            LBL_START_TRIP = generalFunc.retrieveLangLBl("", "LBL_START_TRIP");
                            LBL_DECLINE_JOB = generalFunc.retrieveLangLBl("", "LBL_DECLINE_JOB");
                            LBL_CANCEL_TRIP = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TRIP");
                            LBL_START_JOB = generalFunc.retrieveLangLBl("", "LBL_START_JOB");
                            LBL_CANCEL_JOB = generalFunc.retrieveLangLBl("", "LBL_CANCEL_JOB");
                            LBL_ACCEPT = generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT");
                            LBL_DECLINE = generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT");
                            LBL_INTERCITY_RETURN_LOCATION = generalFunc.retrieveLangLBl("", "LBL_INTERCITY_RETURN_LOCATION");
                        }

                        for (int i = 0; i < arr_rides.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);
                            HashMap<String, String> map = new HashMap<>();

                            map.put("eJobType", generalFunc.getJsonValueStr("eJobType", obj_temp));

                            map.put("vBookingType", generalFunc.getJsonValueStr("vBookingType", obj_temp));
                            map.put("vPhone", generalFunc.getJsonValueStr("vPhone", obj_temp));
                            map.put("vImage", generalFunc.getJsonValueStr("vImage", obj_temp));
                            map.put("vAvgRating", "" + GeneralFunctions.parseFloatValue(0, generalFunc.getJsonValueStr("vAvgRating", obj_temp)));
                            map.put("vTimeZone", generalFunc.getJsonValueStr("vTimeZone", obj_temp));
                            map.put("tSaddress", generalFunc.getJsonValueStr("tSaddress", obj_temp));
                            map.put("tDaddress", generalFunc.getJsonValueStr("tDaddress", obj_temp));
                            map.put("vRideNo", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vRideNo", obj_temp)));
                            map.put("vName", generalFunc.getJsonValueStr("vName", obj_temp));
                            map.put("iTripId", generalFunc.getJsonValueStr("iTripId", obj_temp));
                            map.put("iCabBookingId", generalFunc.getJsonValueStr("iCabBookingId", obj_temp));
                            map.put("vServiceTitle", generalFunc.getJsonValueStr("vServiceTitle", obj_temp));
                            map.put("vVehicleType", generalFunc.getJsonValueStr("vVehicleType", obj_temp));
                            map.put("driverStatus", generalFunc.getJsonValueStr("driverStatus", obj_temp));
                            map.put("eShowHistory", generalFunc.getJsonValueStr("eShowHistory", obj_temp));
                            map.put("eHailTrip", generalFunc.getJsonValueStr("eHailTrip", obj_temp));

                            //InterCity
                            map.put("eIsInterCity", generalFunc.getJsonValueStr("eIsInterCity", obj_temp));
                            map.put("eRoundTrip", generalFunc.getJsonValueStr("eRoundTrip", obj_temp));
                            map.put("tDropAddress", Utils.checkText(generalFunc.getJsonValueStr("tDropAddress", obj_temp)) ? generalFunc.getJsonValueStr("tDropAddress", obj_temp) : "");
                            map.put("tReturnAddress", Utils.checkText(generalFunc.getJsonValueStr("tReturnAddress", obj_temp)) ? generalFunc.getJsonValueStr("tReturnAddress", obj_temp) : "");

                            String eType = generalFunc.getJsonValueStr("eType", obj_temp);
                            String iActive = generalFunc.getJsonValueStr("iActive", obj_temp);
                            String dBooking_dateOrig = generalFunc.getJsonValueStr("dBooking_dateOrig", obj_temp);

                            map.put("currenteType", eType);
                            map.put("eType", eType);
                            map.put("eTypeVal", eType);
                            map.put("iActive", iActive);
                            map.put("iActiveDisplay", generalFunc.getJsonValueStr("iActiveDisplay", obj_temp));

                            map.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));
                            map.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));

                            map.put("iRentalPackageId", generalFunc.getJsonValueStr("iRentalPackageId", obj_temp));
                            map.put("iVehicleTypeId", generalFunc.getJsonValueStr("iVehicleTypeId", obj_temp));
                            map.put("vLatitude", generalFunc.getJsonValueStr("vLatitude", obj_temp));
                            map.put("vLongitude", generalFunc.getJsonValueStr("vLongitude", obj_temp));
                            map.put("vPhone", generalFunc.getJsonValueStr("vPhone", obj_temp));
                            map.put("vCode", generalFunc.getJsonValueStr("vCode", obj_temp));
                            map.put("vPackageName", generalFunc.getJsonValueStr("vPackageName", obj_temp));
                            map.put("moreServices", generalFunc.getJsonValueStr("moreServices", obj_temp));
                            map.put("is_rating", generalFunc.getJsonValueStr("is_rating", obj_temp));
                            map.put("eFavDriver", generalFunc.getJsonValueStr("eFavDriver", obj_temp));
                            map.put("currencySymbol", currencySymbol);
                            String iFare = generalFunc.getJsonValueStr("iFare", obj_temp);
                            String iFareNew = generalFunc.getJsonValueStr("iFareNew", obj_temp);
                            map.put("iFare", generalFunc.convertNumberWithRTL(Utils.checkText(iFareNew) ? iFareNew : currencySymbol + iFare));

                            if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                                map.put("tDaddress", "");
                            }

                            map.put("showViewRequestedServicesBtn", generalFunc.getJsonValueStr("showViewRequestedServicesBtn", obj_temp));
                            map.put("showCancelBookingBtn", generalFunc.getJsonValueStr("showCancelBookingBtn", obj_temp));
                            map.put("showReScheduleBtn", generalFunc.getJsonValueStr("showReScheduleBtn", obj_temp));
                            map.put("showReBookingBtn", generalFunc.getJsonValueStr("showReBookingBtn", obj_temp));
                            map.put("showViewCancelReasonBtn", generalFunc.getJsonValueStr("showViewCancelReasonBtn", obj_temp));
                            map.put("showViewDetailBtn", generalFunc.getJsonValueStr("showViewDetailBtn", obj_temp));
                            map.put("showLiveTrackBtn", generalFunc.getJsonValueStr("showLiveTrackBtn", obj_temp));
                            map.put("showAcceptBtn", generalFunc.getJsonValueStr("showAcceptBtn", obj_temp));
                            map.put("showDeclineBtn", generalFunc.getJsonValueStr("showDeclineBtn", obj_temp));
                            map.put("showStartBtn", generalFunc.getJsonValueStr("showStartBtn", obj_temp));
                            map.put("showCancelBtn", generalFunc.getJsonValueStr("showCancelBtn", obj_temp));

                            //ScheduleBooking
                            if (obj_temp.has("eShownMessage")) {
                                map.put("eShownMessage", generalFunc.getJsonValueStr("eShownMessage", obj_temp));
                            }

                            /*try {
                                map.put("ConvertedTripRequestDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBooking_dateOrig, Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                                map.put("ConvertedTripRequestTime", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBooking_dateOrig, Utils.OriginalDateFormate, DateTimeUtils.OriginalTimeFormat)));
                            } catch (Exception e) {
                                Logger.e("Exception", "::" + e.getMessage());
                                map.put("ConvertedTripRequestDate", "");
                                map.put("ConvertedTripRequestTime", "");
                            }*/
                            map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                            map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_temp));
                            map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));


                            if (eType.equalsIgnoreCase("deliver") || eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                                map.put("LBL_PICK_UP_LOCATION", LBL_SENDER_LOCATION);
                                map.put("LBL_DEST_LOCATION", LBL_RECEIVER_LOCATION);
                            } else {
                                map.put("LBL_PICK_UP_LOCATION", LBL_PICK_UP_LOCATION);
                                map.put("LBL_DEST_LOCATION", LBL_DEST_LOCATION);
                            }

                            map.put("LBL_BOOKING_NO", LBL_BOOKING);
                            map.put("LBL_Status", LBL_Status);

                            if (isDeliver(eType) || eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                                map.put("LBL_BOOKING_NO", LBL_DELIVERY_NO);
                            } else {
                                map.put("LBL_BOOKING_NO", LBL_BOOKING);
                            }
                            map.put("LBL_JOB_LOCATION_TXT", LBL_JOB_LOCATION_TXT);
                            map.put("LBL_CANCEL_BOOKING", LBL_CANCEL_BOOKING);
                            map.put("LBL_RENTAL_CATEGORY_TXT", LBL_RENTAL_CATEGORY_TXT);
                            map.put("liveTrackLBL", LBL_MULTI_LIVE_TRACK_TEXT);
                            map.put("viewDetailLBL", LBL_VIEW_DETAILS);
                            map.put("LBL_VIEW_REASON", LBL_VIEW_REASON);
                            map.put("LBL_RESCHEDULE", LBL_RESCHEDULE);
                            map.put("LBL_REBOOKING", LBL_REBOOKING);
                            map.put("LBL_VIEW_REQUESTED_SERVICES", LBL_VIEW_REQUESTED_SERVICES);
                            map.put("LBL_ACCEPT_JOB", LBL_ACCEPT_JOB);
                            map.put("LBL_ACCEPT", LBL_ACCEPT);
                            map.put("LBL_DECLINE", LBL_DECLINE);
                            map.put("LBL_START_TRIP", LBL_START_TRIP);
                            map.put("LBL_DECLINE_JOB", LBL_DECLINE_JOB);
                            map.put("LBL_CANCEL_TRIP", LBL_CANCEL_TRIP);
                            map.put("LBL_CANCEL_JOB", LBL_CANCEL_JOB);
                            map.put("LBL_START_JOB", LBL_START_JOB);
                            map.put("LBL_INTERCITY_RETURN_LOCATION", LBL_INTERCITY_RETURN_LOCATION);
                            map.put("JSON", obj_temp.toString());

                            if (ServiceModule.IsTrackingProvider) {
                                if (generalFunc.getJsonValueStr("TripType", obj_temp).equalsIgnoreCase("Pickup")) {
                                    map.put("LBL_BOOKING_NO", generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_TRIP_TYPE_PICKUP_TXT"));
                                } else {
                                    map.put("LBL_BOOKING_NO", generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_TRIP_TYPE_DROPOFF_TXT"));
                                }

                                map.put("vRideNo", "");
                                map.put("iActiveDisplay", generalFunc.getJsonValueStr("TripStatus", obj_temp));

                                map.put("vModel", generalFunc.getJsonValueStr("vModel", obj_temp));
                                map.put("vLicencePlateNo", generalFunc.getJsonValueStr("vLicencePlateNo", obj_temp));

                                map.put("LBL_PICK_UP_LOCATION", generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_START_LOC_TXT"));
                                map.put("tSaddress", generalFunc.getJsonValueStr("tStartAddress", obj_temp));
                                map.put("LBL_DEST_LOCATION", generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_END_LOC_TXT"));
                                map.put("tDaddress", generalFunc.getJsonValueStr("tEndAddress", obj_temp));
                                dBooking_dateOrig = generalFunc.getJsonValueStr("dAddedDate", obj_temp);
                                /*try {
                                    map.put("ConvertedTripRequestDate", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBooking_dateOrig, Utils.OriginalDateFormate, DateTimeUtils.DateFormat)));
                                    map.put("ConvertedTripRequestTime", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(dBooking_dateOrig, Utils.OriginalDateFormate, DateTimeUtils.OriginalTimeFormat)));
                                } catch (Exception e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                    map.put("ConvertedTripRequestDate", "");
                                    map.put("ConvertedTripRequestTime", "");
                                }*/
                                map.put("tDisplayDate", generalFunc.getJsonValueStr("tDisplayDate", obj_temp));
                                map.put("tDisplayTime", generalFunc.getJsonValueStr("tDisplayTime", obj_temp));
                                map.put("tDisplayDateTime", generalFunc.getJsonValueStr("tDisplayDateTime", obj_temp));
                            }
                            list.add(map);
                        }
                    }

                    list.get(0).put("TripCount", generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TripCount", responseObj)));
                    String TotalEarning = generalFunc.getJsonValueStr("TotalEarning", responseObj);
                    String TotalEarningAmount = generalFunc.getJsonValueStr("TotalEarningAmount", responseObj);
                    list.get(0).put("TotalEarning", Utils.checkText(TotalEarningAmount) ? generalFunc.convertNumberWithRTL(TotalEarningAmount) : (currencySymbol + generalFunc.convertNumberWithRTL(TotalEarning)));
                    list.get(0).put("AvgRating", "" + GeneralFunctions.parseFloatValue(0, generalFunc.getJsonValueStr("AvgRating", responseObj)));

                    buildFilterTypes(responseObj);

                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }

                } else {
                    buildFilterTypes(responseObj);

                    if (list.size() == 1) {
                        removeNextPageConfig();
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                        noRidesTxt.setVisibility(View.VISIBLE);

                        list.get(0).put("TripCount", "--");
                        list.get(0).put("TotalEarning", "--");
                        list.get(0).put("AvgRating", "--");
                    }
                }
            } else {
                if (!isLoadMore) {
                    buildFilterTypes(responseObj);
                    generateErrorView();

                    if (list.size() == 1) {
                        removeNextPageConfig();
                        noRidesTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                        noRidesTxt.setVisibility(View.VISIBLE);

                        list.get(0).put("TripCount", "--");
                        list.get(0).put("TotalEarning", "--");
                        list.get(0).put("AvgRating", "--");
                    }
                }
            }
            myBookingsRecyclerAdapter.notifyDataSetChanged();
            mIsLoading = false;
            SkeletonView(false);
        });

    }

    private void SkeletonView(boolean isShow) {
        if (myBookingFragment != null && myBookingFragment.checkCurrentFragment(1) || bookingAct != null && bookingAct.checkCurrentFragment(1)) {
            if (isShow) {
                //SkeletonViewHandler.getInstance().ShowNormalSkeletonView(view.findViewById(R.id.llMainArea), R.layout.skeleton_your_bookings);
                SkeletonViewHandler.getInstance().showListSkeletonView(myBookingsRecyclerView, R.layout.skeleton_your_bookings, myBookingsRecyclerAdapter);
            } else {
                SkeletonViewHandler.getInstance().hideSkeletonView();
            }
        }
    }

    private void buildFilterTypes(JSONObject responseObj) {
        if (responseObj == null) return;
        String eFilterSel = generalFunc.getJsonValueStr("eFilterSel", responseObj);

        JSONArray arr_type_filter = generalFunc.getJsonArray("AppTypeFilterArr", responseObj);

        filterlist = new ArrayList<>();
        if (arr_type_filter != null && arr_type_filter.length() > 0) {
            for (int i = 0; i < arr_type_filter.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(arr_type_filter, i);
                HashMap<String, String> map = new HashMap<>();
                String vTitle = generalFunc.getJsonValueStr("vTitle", obj_temp);
                map.put("vTitle", vTitle);
                String vFilterParam = generalFunc.getJsonValueStr("vFilterParam", obj_temp);
                map.put("vFilterParam", vFilterParam);

                filterlist.add(map);
            }
        }
        if (myBookingFragment != null) {
            myBookingFragment.filterManage(filterlist);
        } else {
            bookingAct.filterManage(filterlist);
        }

        JSONArray subFilterOptionArr = generalFunc.getJsonArray("subFilterOption", responseObj);

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
                        myBookingFragment.selSubFilterType = eFilterSel;
                        myBookingFragment.subFilterPosition = i;
                    } else {
                        bookingAct.selSubFilterType = eFilterSel;
                        bookingAct.subFilterPosition = i;
                    }
                }
                if (eFilterSel.equalsIgnoreCase("past")) {
                    calenderHeaderLayout.setVisibility(View.VISIBLE);
                    list.get(0).put("isPast", "yes");
                } else {
                    calenderHeaderLayout.setVisibility(View.GONE);
                    list.get(0).put("isPast", "no");
                }
                subFilterlist.add(map);
            }
        }
        if (myBookingFragment != null) {
            myBookingFragment.subFilterManage(subFilterlist, "History");
        } else {
            bookingAct.subFilterManage(subFilterlist, "History");
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
                        getBookingsHistory(false);
                    }
                }

            } catch (ParseException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }


        });
    }

    private class setOnClickList implements View.OnClickListener {

        boolean isTripItemClick = false;
        int tripItemPosition = 0;

        @Override
        public void onClick(View view) {
            Utils.hideKeyboard(getActContext());
            if (isTripItemClick) {
                Bundle bn = new Bundle();
                bn.putString("iTripId", list.get(tripItemPosition).get("iTripId"));
                new ActUtils(getActivity()).startActForResult(RideHistoryDetailActivity.class, bn, HISTORYDETAILS);
            } else {
                int i = view.getId();
                if (i == R.id.backImgView) {
                    requireActivity().onBackPressed();
                } else if (i == R.id.filterArea) {
                    if (myBookingFragment != null) {
                        myBookingFragment.BuildType("History");
                    } else {
                        bookingAct.BuildType("History");
                    }
                }
            }
        }
    }

    private void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        myBookingsRecyclerAdapter.removeFooterView();
    }

    private void generateErrorView() {
        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getBookingsHistory(false));
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
        getBookingsHistory(false);
    }
}