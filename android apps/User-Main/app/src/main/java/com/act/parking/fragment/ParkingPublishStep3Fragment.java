package com.act.parking.fragment;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.act.parking.ParkingPublish;
import com.act.parking.adapter.ParkingDatesRecyclerAdapter;
import com.act.parking.adapter.ParkingTimeSlotAdapter;
import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentParkingPublishStep3Binding;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONObject;

import java.text.DateFormatSymbols;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Locale;

public class ParkingPublishStep3Fragment extends BaseFragment implements ParkingTimeSlotAdapter.setRecentTimeSlotClickList, ParkingDatesRecyclerAdapter.OnDateSelectListener {

    FragmentParkingPublishStep3Binding binding;
    private ParkingPublish mActivity;
    boolean needUpdate = false;
    private GeneralFunctions generalFunc;
    private ParkingDatesRecyclerAdapter dateAdapter;
    ArrayList<HashMap<String, String>> timeSlotList;
    ParkingTimeSlotAdapter timeSlotAdapter;
    ArrayList<HashMap<String, Object>> dateList = new ArrayList<>();
    private String selectedDay;
    private ArrayList<String> availableSlotsArray = new ArrayList<>();
    private ArrayList<String> oldSlotsArray = new ArrayList<>();
    private final ArrayList<String> daylist = new ArrayList<>();
    private boolean isCheckPageNext = false;
    private boolean isCheckPagePrevious = false;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_parking_publish_step_3, container, false);

        setlabels();
        setdata();
        return binding.getRoot();
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof ParkingPublish) {
            mActivity = (ParkingPublish) requireActivity();
            generalFunc = mActivity.generalFunc;
        }
    }

    private void getDisplayTimeSlots() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DisplayAvailabilityForParking");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vDay", selectedDay);

        ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    JSONObject obj = generalFunc.getJsonObject(responseString);
                    JSONObject message_obj = generalFunc.getJsonObject("message", obj);
                    availableSlotsArray.clear();
                    oldSlotsArray.clear();
                    if (Utils.checkText(generalFunc.getJsonValueStr("vAvailableTimes", message_obj))) {
                        availableSlotsArray = new ArrayList<>(Arrays.asList(generalFunc.getJsonValueStr("vAvailableTimes", message_obj).split(",")));
                        oldSlotsArray = new ArrayList<>(Arrays.asList(generalFunc.getJsonValueStr("vAvailableTimes", message_obj).split(",")));
                    }
                    timeSlotAdapter.updateSelectedSlots(availableSlotsArray);
                    timeSlotAdapter.notifyDataSetChanged();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void UpdateTimeSlots() {
        String vAvailableTimes = String.join(",", availableSlotsArray);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateAvailabilityForParking");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vDay", selectedDay);
        parameters.put("vAvailableTimes", vAvailableTimes);

        ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (isCheckPageNext) {
                        isCheckPageNext = false;
                        if (generalFunc.getJsonValue("AvailabilityAdded", responseString).equalsIgnoreCase("yes")) {
                            mActivity.setPageNext();
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_SELECT_PARKING_SPACE_AVAILABILITY_TXT"), buttonId -> {
                            });
                        }
                    } else if (isCheckPagePrevious) {
                        isCheckPagePrevious = false;
                        mActivity.setPagePrevious();
                    } else {
                        getDisplayTimeSlots();
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }


    private void setdata() {
        timeSlotList = new ArrayList<>();

        settimeSlotData();

        binding.timeSlotsRecyclerview.setLayoutManager(new GridLayoutManager(mActivity, 3));
        timeSlotAdapter = new ParkingTimeSlotAdapter(mActivity, timeSlotList, availableSlotsArray);
        binding.timeSlotsRecyclerview.setAdapter(timeSlotAdapter);
        timeSlotAdapter.setOnClickList(this);

        Calendar startDate = Calendar.getInstance(MyUtils.getLocale());
        startDate.add(Calendar.MONTH, 0);
        Calendar endDate = Calendar.getInstance(MyUtils.getLocale());
        endDate.add(Calendar.MONTH, 1);
        Date currentTempDate = startDate.getTime();
        Locale locale = MyUtils.getLocale();
        java.text.DateFormat dayNameFormatter = new SimpleDateFormat("EEE", locale);
        int position = 0;
        ArrayList<String> daysarray = new ArrayList<>();
        String[] passnamesOfDays;
        if (generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY).equalsIgnoreCase("th")) {
            passnamesOfDays = DateFormatSymbols.getInstance(locale).getShortWeekdays();
        } else {
            passnamesOfDays = DateFormatSymbols.getInstance(locale).getWeekdays();
        }
        for (int i = 0; i < passnamesOfDays.length; i++) {
            if (i != 0) {
                daylist.add(passnamesOfDays[i]);
            }
        }
        selectedDay = daylist.get(0);
        while (currentTempDate.before(endDate.getTime())) {
            HashMap<String, Object> hashMap = new HashMap<>();
            hashMap.put("dayNameTxt", dayNameFormatter.format(currentTempDate));

            if (!daysarray.contains(dayNameFormatter.format(currentTempDate))) {
                daysarray.add(dayNameFormatter.format(currentTempDate));
                dateList.add(hashMap);
            }

            position = position + 1;

            Calendar tmpCal = Calendar.getInstance(MyUtils.getLocale());

            tmpCal.add(Calendar.DATE, position);

            currentTempDate = tmpCal.getTime();

        }


        dateAdapter = new ParkingDatesRecyclerAdapter(generalFunc, daylist, mActivity, selectedDay);
        dateAdapter.setOnDateSelectListener(this);
        binding.datesRecyclerView.setLayoutManager(new LinearLayoutManager(mActivity, LinearLayoutManager.HORIZONTAL, true));
        binding.datesRecyclerView.setAdapter(dateAdapter);

        if (!generalFunc.isRTLmode()) {
            binding.datesRecyclerView.setLayoutDirection(View.LAYOUT_DIRECTION_RTL);
        } else {
            binding.datesRecyclerView.setLayoutDirection(View.LAYOUT_DIRECTION_LTR);
        }

        dateAdapter.notifyDataSetChanged();
    }

    public void settimeSlotData() {

        String LBL_AM_TXT = generalFunc.retrieveLangLBl("am", "LBL_AM_TXT");
        String LBL_PM_TXT = generalFunc.retrieveLangLBl("pm", "LBL_PM_TXT");

        for (int i = 0; i <= 23; i++) {
            HashMap<String, String> map = new HashMap<>();
            HashMap<String, String> mapOrig = new HashMap<>();

            map.put("status", "no");
            mapOrig.put("status", "no");

            int fromtime = i;
            int toTime = i + 1;


            String fromtimedisp = "";
            String Totimedisp = "";
            String selfromtime = "";
            String seltoTime = "";

            if (fromtime == 0) {
                fromtime = 12;
            }

            if (fromtime < 10) {
                selfromtime = "0" + fromtime;
            } else {
                selfromtime = fromtime + "";
            }

            if (toTime < 10) {
                seltoTime = "0" + toTime;
            } else {
                seltoTime = toTime + "";
            }

            if (i < 12) {

                if (fromtime < 10) {
                    fromtimedisp = "0" + fromtime;

                } else {
                    fromtimedisp = fromtime + "";

                }

                if (toTime < 10) {
                    Totimedisp = "0" + toTime;

                } else {
                    Totimedisp = toTime + "";
                }
                map.put("name", generalFunc.convertNumberWithRTL(fromtimedisp + " " + LBL_AM_TXT + " - " + Totimedisp + " " + generalFunc.retrieveLangLBl(i == 11 ? "pm" : "am", i == 11 ? "LBL_PM_TXT" : "LBL_AM_TXT")));
                map.put("selname", generalFunc.convertNumberWithRTL(selfromtime + "-" + seltoTime));
            } else {

                fromtime = fromtime % 12;
                toTime = toTime % 12;
                if (fromtime == 0) {
                    fromtime = 12;
                }

                if (toTime == 0) {
                    toTime = 12;
                }
                if (fromtime < 10) {
                    fromtimedisp = "0" + fromtime;
                } else {
                    fromtimedisp = fromtime + "";
                }

                if (toTime < 10) {
                    Totimedisp = "0" + toTime;
                } else {
                    Totimedisp = toTime + "";
                }

                map.put("name", generalFunc.convertNumberWithRTL(fromtimedisp + " " + LBL_PM_TXT + " - " + Totimedisp + " " + generalFunc.retrieveLangLBl(i == 23 ? "am" : "pm", i == 23 ? "LBL_AM_TXT" : "LBL_PM_TXT")));
                map.put("selname", generalFunc.convertNumberWithRTL(selfromtime + "-" + seltoTime));
            }

            if (DateTimeUtils.Is24HourTime) {
                if (i == 0) {
                    selfromtime = "00";
                }
                map.put("name", generalFunc.convertNumberWithRTL(selfromtime + "-" + seltoTime));
            }


            timeSlotList.add(map);
        }

    }

    @Override
    public void onResume() {
        super.onResume();
        getDisplayTimeSlots();
    }

    private void setlabels() {
        binding.selectDayHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SELECT_DAT_TXT"));
        binding.selectTimeHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SELECT_TIME_TXT"));
    }

    @Override
    public void itemTimeSlotLocClick(int position) {

        if (availableSlotsArray.size() > 0 && availableSlotsArray.contains(timeSlotList.get(position).get("name"))) {
            availableSlotsArray.remove(timeSlotList.get(position).get("name"));
        } else {
            availableSlotsArray.add(timeSlotList.get(position).get("name"));
        }

    }

    @Override
    public void onDateSelect(int position) {
        dateAdapter.setSelectedSeat(position);
        if (!availableSlotsArray.equals(oldSlotsArray)) {
            UpdateTimeSlots();
            selectedDay = daylist.get(position);
            dateAdapter.setSelectedDate(selectedDay);
            dateAdapter.notifyDataSetChanged();
        } else {
            selectedDay = daylist.get(position);
            dateAdapter.setSelectedDate(selectedDay);
            dateAdapter.notifyDataSetChanged();
            getDisplayTimeSlots();

        }

    }

    public void checkPageNext() {
        if (mActivity != null) {
            isCheckPageNext = true;
            UpdateTimeSlots();
        }
    }


    public void checkPagePrevious() {
        if (mActivity != null) {
            if (availableSlotsArray.equals(oldSlotsArray)) {
                isCheckPagePrevious = true;
                UpdateTimeSlots();
            } else {
                mActivity.setPagePrevious();
            }
        }
    }
}
