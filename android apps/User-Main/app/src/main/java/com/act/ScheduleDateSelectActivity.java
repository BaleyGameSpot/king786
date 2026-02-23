package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.adapter.files.DatesRecyclerAdapter;
import com.adapter.files.TimeSlotAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityScheduleDateSelectBinding;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;

public class ScheduleDateSelectActivity extends ParentActivity implements TimeSlotAdapter.setRecentTimeSlotClickList, DatesRecyclerAdapter.OnDateSelectListener {

    private ActivityScheduleDateSelectBinding binding;
    private MButton continueBtn;

    private final ArrayList<HashMap<String, Object>> dateList = new ArrayList<>();

    private TimeSlotAdapter timeSlotAdapter;
    private final ArrayList<HashMap<String, String>> timeSlotList = new ArrayList<>();
    private final ArrayList<HashMap<String, String>> timeSlotListOrig = new ArrayList<>();

    private final HashMap<String, List<String>> driverAvailDataMap = new HashMap<>();
    private int datePosition = 0;

    private String Stime = "", iCabBookingId = "", SERVICE_PROVIDER_FLOW = "";
    private String seldate = "", seltime = "";

    private boolean ismain = false;

    @SuppressLint("NotifyDataSetChanged")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_schedule_date_select);

        SERVICE_PROVIDER_FLOW = generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", obj_userProfile);

        if (getIntent().getStringExtra("iCabBookingId") != null) {
            iCabBookingId = getIntent().getStringExtra("iCabBookingId");
        }

        if (getIntent().getStringExtra("Stime") != null) {
            Stime = getIntent().getStringExtra("Stime");
        }

        ismain = getIntent().getBooleanExtra("isMain", false);

        initViews();
        setTimeSlotData();

        binding.loadingAvailBar.setVisibility(View.GONE);

        if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
            binding.headerTxt.setVisibility(View.GONE);
            binding.addressTxt.setVisibility(View.GONE);
            seldate = Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, new Date());
            getDriverAvailability(false);
        } else {
            onDateSelect(datePosition, false);
        }
    }

    private void initViews() {
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_BOOKING_DATE"));

        binding.headerTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_ADDRESS_HINT_INFO"));
        binding.addressTxt.setText(getIntent().getStringExtra("address"));

        binding.monthTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WHAT_DAY"));
        binding.timeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WHAT_TIME"));

        //
        Calendar startDate = Calendar.getInstance(MyUtils.getLocale());
        startDate.add(Calendar.MONTH, 0);
        Calendar endDate = Calendar.getInstance(MyUtils.getLocale());
        endDate.add(Calendar.MONTH, 1);

        Date currentTempDate = startDate.getTime();
        int position = 0;

        Locale locale = MyUtils.getLocale();
        DateFormat dayNameFormatter = new SimpleDateFormat("EEE", locale);
        DateFormat dayNumFormatter = new SimpleDateFormat("dd", locale);
        DateFormat dayMonthFormatter = new SimpleDateFormat("MMM", locale);

        while (currentTempDate.before(endDate.getTime())) {
            HashMap<String, Object> hashMap = new HashMap<>();
            hashMap.put("dayNameTxt", dayNameFormatter.format(currentTempDate));
            hashMap.put("dayNumTxt", dayNumFormatter.format(currentTempDate) + " " + dayMonthFormatter.format(currentTempDate));
            hashMap.put("currentDate", currentTempDate);
            dateList.add(hashMap);
            position = position + 1;
            Calendar tmpCal = Calendar.getInstance(MyUtils.getLocale());
            tmpCal.add(Calendar.DATE, position);
            currentTempDate = tmpCal.getTime();
        }

        binding.rvDates.setAdapter(new DatesRecyclerAdapter(getActContext(), dateList, startDate.getTime(), this));

        //
        timeSlotAdapter = new TimeSlotAdapter(generalFunc, timeSlotList, this);
        binding.rvTimeSlot.setAdapter(timeSlotAdapter);

        //
        continueBtn = ((MaterialRippleLayout) binding.continueBtn).getChildView();
        addToClickHandler(continueBtn);
        continueBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
    }

    private Context getActContext() {
        return ScheduleDateSelectActivity.this;
    }

    @Override
    public void itemTimeSlotLocClick(int position) {
        seltime = timeSlotListOrig.get(position).get("selname");
        Stime = timeSlotListOrig.get(position).get("name");
    }

    private void CheckDateTimeApi() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CheckScheduleTimeAvailability");
        parameters.put("scheduleDate", seldate + " " + seltime);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    Bundle bundle = new Bundle();
                    bundle.putString("SelectedVehicleTypeId", getIntent().getStringExtra("SelectedVehicleTypeId"));
                    bundle.putString("Quantity", getIntent().getStringExtra("Quantity"));
                    bundle.putBoolean("isufx", true);
                    bundle.putString("latitude", getIntent().getStringExtra("latitude"));
                    bundle.putString("longitude", getIntent().getStringExtra("longitude"));
                    bundle.putString("address", getIntent().getStringExtra("address"));
                    bundle.putString("SelectDate", seldate + " " + seltime);
                    bundle.putString("SelectvVehicleType", getIntent().getStringExtra("SelectvVehicleType"));
                    bundle.putString("SelectvVehiclePrice", getIntent().getStringExtra("SelectvVehiclePrice"));
                    bundle.putString("iUserAddressId", getIntent().getStringExtra("iUserAddressId"));
                    bundle.putString("Quantityprice", getIntent().getStringExtra("Quantityprice"));
                    bundle.putString("type", Utils.CabReqType_Later);

                    bundle.putString("Sdate", generalFunc.getDateFormatedType(seldate, Utils.DefaultDatefromate, Utils.dateFormateForBooking));
                    bundle.putString("Stime", Stime);
                    bundle.putString("iCabBookingId", iCabBookingId);

                    boolean isFrom = (getCallingActivity() != null && (getCallingActivity().getClassName().equals(CarWashBookingDetailsActivity.class.getName())) ||
                            (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider") && getCallingActivity().getClassName().equals(BookingActivity.class.getName())) ||
                            (getCallingActivity().getClassName().equals(UberXHomeActivity.class.getName())));

                    if (ismain || isFrom) {
                        new ActUtils(getActContext()).setOkResult(bundle);
                        finish();
                    } else {
                        bundle.putBoolean("isRebooking", getIntent().getBooleanExtra("isRebooking", false));
                        bundle.putString("selType", Utils.CabGeneralType_UberX);
                        new ActUtils(getActContext()).startActWithData(MainActivity.class, bundle);
                    }

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void setTimeSlotData() {

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

                mapOrig.put("selname", selfromtime + "-" + seltoTime);
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

                mapOrig.put("selname", selfromtime + "-" + seltoTime);
            }

            if (DateTimeUtils.Is24HourTime) {
                if (i == 0) {
                    selfromtime = "00";
                }
                map.put("name", generalFunc.convertNumberWithRTL(selfromtime + "-" + seltoTime));
            }


            timeSlotList.add(map);
            timeSlotListOrig.add(mapOrig);
        }

    }

    private void getDriverAvailability(boolean isNewData) {
        driverAvailDataMap.clear();
        if (!isNewData) {
            binding.dataArea.setVisibility(View.GONE);
            binding.loadingAvailBar.setVisibility(View.VISIBLE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDriverAvailability");
        parameters.put("iDriverId", getIntent().getStringExtra("iDriverId"));
        parameters.put("AvailabilityDate", seldate);

        ApiHandler.execute(getActContext(), parameters, isNewData, false, generalFunc, responseString -> {

            binding.dataArea.setVisibility(View.VISIBLE);
            binding.loadingAvailBar.setVisibility(View.GONE);

            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    JSONArray message = generalFunc.getJsonArray(Utils.message_str, responseString);
                    if (message != null && message.length() >= 1) {
                        for (int i = 0; i < message.length(); i++) {
                            JSONObject object = generalFunc.getJsonObject(message, i);
                            driverAvailDataMap.put(generalFunc.getJsonValueStr("vDay", object), Arrays.asList(generalFunc.getJsonValueStr("vAvailableTimes", object).split(",")));
                        }
                    }
                    onDateSelect(datePosition, false);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    @SuppressLint("NotifyDataSetChanged")
    @Override
    public void onDateSelect(int position, boolean isNewData) {
        Date date = (Date) dateList.get(position).get("currentDate");

        String tempDate = Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, date);
        seldate = generalFunc.getDateFormatedType(tempDate, DateTimeUtils.serverDateTimeFormat, Utils.DefaultDatefromate, new Locale("en"));

        datePosition = position;
        if (isNewData) {
            getDriverAvailability(isNewData);
            return;
        }

        DateFormat formatter_day_en = new SimpleDateFormat("EEEE", new Locale("en"));
        String dayName_en = formatter_day_en.format(date);

        if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
            seltime = "";
            Stime = "";

            timeSlotAdapter.isSelectedPos = -1;

            List<String> data_availability = new ArrayList<>();

            if (driverAvailDataMap.get(dayName_en) != null) {
                data_availability.addAll(driverAvailDataMap.get(dayName_en));
            }

            for (int i = 0; i < timeSlotListOrig.size(); i++) {
                HashMap<String, String> map_tmP_orig = timeSlotListOrig.get(i);
                HashMap<String, String> map_tmP = timeSlotList.get(i);

                String selName = map_tmP_orig.get("selname");

                String isDriverAvailable = map_tmP_orig.get("isDriverAvailable");

                map_tmP_orig.put("isDriverAvailable", data_availability.contains(selName) ? "Yes" : "No");
                map_tmP.put("isDriverAvailable", map_tmP_orig.get("isDriverAvailable"));

                if (isDriverAvailable != null && /*!isNotifyAll && */!isDriverAvailable.equalsIgnoreCase(map_tmP_orig.get("isDriverAvailable"))) {
                    timeSlotAdapter.notifyItemChanged(i);
                }
            }

            timeSlotAdapter.notifyDataSetChanged();
        }

    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            ScheduleDateSelectActivity.super.onBackPressed();
        } else if (i == continueBtn.getId()) {
            if (seltime.equalsIgnoreCase("")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please Select Booking Time.", "LBL_SELECT_SERVICE_BOOKING_TIME"));
                return;
            }
            CheckDateTimeApi();
        }
    }
}