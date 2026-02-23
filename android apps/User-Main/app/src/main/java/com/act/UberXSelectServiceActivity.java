package com.act;

import android.app.Activity;
import android.content.Context;
import android.os.Bundle;
import android.text.SpannableStringBuilder;
import android.text.Spanned;
import android.text.method.LinkMovementMethod;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewTreeObserver;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RadioButton;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyClickableSpan;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class UberXSelectServiceActivity extends ParentActivity {


    public MButton btn_type2_rideNow;
    public MButton btn_type2_rideLater;

    MTextView titleTxt, noDataTxt;
    ImageView backImgView;
    LinearLayout serviceLayout;
    RelativeLayout selectServicePage;
    MButton continueBtn;
    String iVehicleCategoryId = "";

    String selectedVehicleTypeId = "1";
    String selectvVehicleType = "";
    String selectvVehiclePrice = "";
    int selectedVehiclePosition = 0;
    ArrayList<View> listOfView = new ArrayList<>();
    boolean isclick = false;
    int QUANTITYdata = 0;
    String QUANTITYPrice = "";
    String FareType = "";
    LinearLayout rideLaterArea;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_uber_xselect_service);


        iVehicleCategoryId = getIntent().getStringExtra("iVehicleCategoryId");

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        noDataTxt = (MTextView) findViewById(R.id.noDataTxt);

        noDataTxt.setText(generalFunc.retrieveLangLBl("No any service Data found.", "LBL_NO_SERVICE_DATA_TXT"));

        selectServicePage = (RelativeLayout) findViewById(R.id.selectServicePage);
        backImgView = (ImageView) findViewById(R.id.backImgView);

        serviceLayout = (LinearLayout) findViewById(R.id.servicesAreaLayout);
        continueBtn = (MButton) findViewById(R.id.continueBtn);
        continueBtn.setText(generalFunc.retrieveLangLBl("Continue", "LBL_CONTINUE_BTN"));
        btn_type2_rideNow = ((MaterialRippleLayout) findViewById(R.id.btn_type2_rideNow)).getChildView();
        btn_type2_rideLater = ((MaterialRippleLayout) findViewById(R.id.btn_type2_rideLater)).getChildView();
        rideLaterArea = (LinearLayout) findViewById(R.id.rideLaterArea);


        btn_type2_rideNow.setText(generalFunc.retrieveLangLBl("Book Now", "LBL_BOOK_NOW"));
        btn_type2_rideLater.setText(generalFunc.retrieveLangLBl("Book Later", "LBL_BOOK_LATER"));
        addToClickHandler(btn_type2_rideLater);
        addToClickHandler(btn_type2_rideNow);
        addToClickHandler(continueBtn);

        setData();
        intializeServiceCategoryTypes(iVehicleCategoryId);


        if (generalFunc.getJsonValueStr("RIDE_LATER_BOOKING_ENABLED", obj_userProfile).equalsIgnoreCase("Yes")) {
            rideLaterArea.setVisibility(View.VISIBLE);
        } else {
            rideLaterArea.setVisibility(View.GONE);
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        isclick = false;
    }

    private void setData() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_SERVICE"));
        addToClickHandler(backImgView);
    }

    private void intializeServiceCategoryTypes(String iVehicleCategoryId) {

        final ArrayList<View> listForViews = new ArrayList<>();
        final LayoutInflater mInflater = (LayoutInflater)
                getActContext().getSystemService(Activity.LAYOUT_INFLATER_SERVICE);

        noDataTxt.setVisibility(View.GONE);
        HashMap<String, String> parameters = new HashMap<String, String>();

        parameters.put("type", "getServiceCategoryTypes");
        parameters.put("iVehicleCategoryId", iVehicleCategoryId);
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("vLatitude", getIntent().getStringExtra("latitude"));
        parameters.put("vLongitude", getIntent().getStringExtra("longitude"));

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);


            if (responseObj != null && !responseObj.toString().equals("")) {

                boolean isDataAvail = generalFunc.checkDataAvail(Utils.action_str, responseObj);

                boolean viewMoreSet = false;
                if (isDataAvail == true) {

                    String vCategoryTitle = generalFunc.getJsonValueStr("vCategoryTitle", responseObj);
                    if (!vCategoryTitle.trim().equals("")) {
                        Spanned descdata = generalFunc.fromHtml(vCategoryTitle);
                        ((MTextView) findViewById(R.id.vCategoryTitleTxtView)).setText(generalFunc.fromHtml(descdata + ""));
                    } else {
                        (findViewById(R.id.vCategoryTitleTxtView)).setVisibility(View.GONE);
                    }

                    String vCategoryDesc = generalFunc.getJsonValueStr("vCategoryDesc", responseObj);
                    if (!vCategoryDesc.trim().equals("")) {
                        Spanned descdata = generalFunc.fromHtml(vCategoryDesc);
                        ((MTextView) findViewById(R.id.descTxtView)).setText(generalFunc.fromHtml(descdata + ""));
                        if (viewMoreSet == false) {
                            generalFunc.makeTextViewResizable((MTextView) findViewById(R.id.descTxtView), 6, "...\n+ " + generalFunc.retrieveLangLBl("View More", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_16);
//                                Enable this if you have html data print issue
//                                makeTextViewResizable((MTextView) findViewById(R.id.descTxtView), 6, "...\n+ " + generalFunc.retrieveLangLBl("View More", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_16,vCategoryDesc,vCategoryTitle);
                            viewMoreSet = true;
                        }
                    } else {
                        (findViewById(R.id.descTxtView)).setVisibility(View.GONE);
                    }


                    final JSONArray mainCataArray = generalFunc.getJsonArray("message", responseObj);

                    if (mainCataArray != null) {

                        String userProfileJson = "", LBL_MINIMUM_TXT = "", LBL_HOURS_TXT = "", LBL_PRICE_PER_KM = "", LBL_PRICE_PER_MILES = "", LBL_PRICE_PER_MINUTE = "", LBL_BASE_FARE_SMALL_TXT = "", LBL_QTY_TXT = "", LBL_FARE_DETAIL_TXT = "", LBL_MIN_FARE = "", LBL_HOUR = "";

                        if (mainCataArray.length() > 0) {
                            userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

                            LBL_MINIMUM_TXT = generalFunc.retrieveLangLBl("", "LBL_MINIMUM_TXT");
                            LBL_HOURS_TXT = generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT");
                            LBL_PRICE_PER_KM = generalFunc.retrieveLangLBl("", "LBL_PRICE_PER_KM");
                            LBL_PRICE_PER_MILES = generalFunc.retrieveLangLBl("", "LBL_PRICE_PER_MILES");
                            LBL_PRICE_PER_MINUTE = generalFunc.retrieveLangLBl("", "LBL_PRICE_PER_MINUTE");
                            LBL_BASE_FARE_SMALL_TXT = generalFunc.retrieveLangLBl("", "LBL_BASE_FARE_SMALL_TXT");
                            LBL_MIN_FARE = generalFunc.retrieveLangLBl("", "LBL_MIN_FARE");
                            LBL_FARE_DETAIL_TXT = generalFunc.retrieveLangLBl("", "LBL_FARE_DETAIL_TXT");
                            LBL_QTY_TXT = generalFunc.retrieveLangLBl("", "LBL_QTY_TXT");
                            LBL_HOUR = generalFunc.retrieveLangLBl("hour", "LBL_HOUR");
                        }

                        for (int i = 0; i < mainCataArray.length(); i++) {
                            final HashMap<String, String> selectAllServiceMap = new HashMap<String, String>();
                            final JSONObject mainCataObject = generalFunc.getJsonObject(mainCataArray, i);

                            String ALLOW_SERVICE_PROVIDER_AMOUNT = generalFunc.getJsonValueStr("ALLOW_SERVICE_PROVIDER_AMOUNT", mainCataObject);

                            ((MTextView) findViewById(R.id.vCategoryTitleTxtView)).setText(generalFunc.fromHtml(generalFunc.getJsonValueStr("vCategoryTitle", mainCataObject)));


                            ((MTextView) findViewById(R.id.selectServiceTxtView)).setText(getIntent().getStringExtra("vCategoryName"));

                            final View view = mInflater.inflate(R.layout.item_service_catagory_design, null);
                            final RelativeLayout countingArea;

                            final ImageView leftImgView = (ImageView) view.findViewById(R.id.leftImgView);
                            ImageView rightImgView = (ImageView) view.findViewById(R.id.rightImgView);
                            final MTextView QTYTxtView = (MTextView) view.findViewById(R.id.QTYTxtView);
                            final MTextView QTYNumberTxtView = (MTextView) view.findViewById(R.id.QTYNumberTxtView);
                            final MTextView priceTxtView = (MTextView) view.findViewById(R.id.priceTxtView);


                            if (generalFunc.isRTLmode()) {
                                leftImgView.setRotationY(180);
                                rightImgView.setRotationY(180);
                            }

                            // Min HOUR AREA START
                            String fMinHour = generalFunc.getJsonValueStr("fMinHour", mainCataObject);
                            final RelativeLayout minHourArea = (RelativeLayout) view.findViewById(R.id.minHourArea);


                            if (!fMinHour.equalsIgnoreCase("") && Integer.parseInt(fMinHour) > 1) {
                                ((MTextView) view.findViewById(R.id.minHourTxtView)).setText("" + "(" + LBL_MINIMUM_TXT + " " + fMinHour + " " + LBL_HOURS_TXT + ")");
                            }//

//                          FARE DETAILS AREA START

                            final LinearLayout fareDetailArea = (LinearLayout) view.findViewById(R.id.fareDetailArea);

                            String eAllowQty = generalFunc.getJsonValueStr("eAllowQty", mainCataObject);
                            if (!generalFunc.getJsonValueStr("eFareType", mainCataObject).equalsIgnoreCase("Fixed")) {
                                eAllowQty = "No";
                            }
                            final String iMaxQty = generalFunc.getJsonValueStr("iMaxQty", mainCataObject);

                            String fPricePerKM = generalFunc.getJsonValueStr("fPricePerKM", mainCataObject);
                            String fPricePerMin = generalFunc.getJsonValueStr("fPricePerMin", mainCataObject);
                            String iBaseFare = generalFunc.getJsonValueStr("iBaseFare", mainCataObject);
                            String iMinFare = generalFunc.getJsonValueStr("iMinFare", mainCataObject);

                            ((MTextView) view.findViewById(R.id.fPricePerKMValueTxtView)).setText(fPricePerKM);
                            ((MTextView) view.findViewById(R.id.fPricePerMinValueTxtView)).setText(fPricePerMin);
                            ((MTextView) view.findViewById(R.id.iBaseFareValueTxtView)).setText(iBaseFare);
                            ((MTextView) view.findViewById(R.id.iMinFareValueTxtView)).setText(iMinFare);


                            if (generalFunc.getJsonValue("eUnit", userProfileJson).equals("KMs")) {
                                ((MTextView) view.findViewById(R.id.fPricePerKMTxt)).setText(LBL_PRICE_PER_KM);
                            } else {
                                ((MTextView) view.findViewById(R.id.fPricePerKMTxt)).setText(LBL_PRICE_PER_MILES);
                            }
                            ((MTextView) view.findViewById(R.id.fPricePerMinTxt)).setText(LBL_PRICE_PER_MINUTE);
                            ((MTextView) view.findViewById(R.id.iBaseFareTxt)).setText(LBL_BASE_FARE_SMALL_TXT);
                            ((MTextView) view.findViewById(R.id.iMinFareTxt)).setText(LBL_MIN_FARE);

                            Logger.d("ALLOW_SERVICE_PROVIDER_AMOUNT", "::" + ALLOW_SERVICE_PROVIDER_AMOUNT);
                            if (generalFunc.getJsonValueStr("eFareType", mainCataObject).equalsIgnoreCase("Regular")) {
                                priceTxtView.setVisibility(View.GONE);
                            } else if (ALLOW_SERVICE_PROVIDER_AMOUNT.equalsIgnoreCase("No")) {
                                Logger.d("priceTxtView", ":: VISIBLE");
                                priceTxtView.setVisibility(View.VISIBLE);
                            } else {
                                priceTxtView.setVisibility(View.GONE);
                            }
//                          OVER FARE DETAILS AREA

                            ((MTextView) view.findViewById(R.id.fareHeadingTxtView)).setText("" + LBL_FARE_DETAIL_TXT);

                            final RadioButton catagoryRadioBtn = (RadioButton) view.findViewById(R.id.catagoryRadioBtn);
                            catagoryRadioBtn.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vVehicleType", mainCataObject)));
                            countingArea = (RelativeLayout) view.findViewById(R.id.countingArea);
                            final LinearLayout uberXServiceAreaLayout = (LinearLayout) view.findViewById(R.id.uberXServiceAreaLayout);

                            QTYNumberTxtView.setText(generalFunc.convertNumberWithRTL("1"));
                            QTYTxtView.setText("" + LBL_QTY_TXT);
                            leftImgView.setImageResource(R.mipmap.ic_left_disabled_rounded);

                            String eFareType = generalFunc.getJsonValueStr("eFareType", mainCataObject);
                            if (eFareType.equalsIgnoreCase("Fixed")) {
                                priceTxtView.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fFixedFare", mainCataObject)));
                            } else if (eFareType.equalsIgnoreCase("Hourly")) {
                                Logger.d("priceTxtView val", "::" + generalFunc.getJsonValueStr("fPricePerHour", mainCataObject) + "/" + LBL_HOUR);
                                priceTxtView.setText(
                                        generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fPricePerHour", mainCataObject)) + "/" + LBL_HOUR);
                            }


                            rightImgView.setOnClickListener(v -> {
                                try {

                                    String qtytempdata = QTYNumberTxtView.getText().toString();
                                    String filterdataqty = qtytempdata.replace(generalFunc.getJsonValueStr("vSymbol", mainCataObject), "");
                                    int QUANTITY = Integer.parseInt(filterdataqty);
                                    QUANTITYdata = QUANTITY;


                                    if (QUANTITY < Integer.parseInt(iMaxQty)) {
                                        if (QUANTITY >= 1) {
                                            QUANTITY = QUANTITY + 1;

                                            String tempdata = generalFunc.getJsonValueStr("fFixedFare_value", mainCataObject);
                                            String filterdata = tempdata.replace(generalFunc.getJsonValueStr("vSymbol", mainCataObject), "");
                                            priceTxtView.setText(generalFunc.getJsonValueStr("vSymbol", mainCataObject) +
                                                    generalFunc.convertNumberWithRTL((QUANTITY * GeneralFunctions.parseDoubleValue(0.0, filterdata)) + ""));
                                            QTYTxtView.setText(generalFunc.retrieveLangLBl("QTY", "LBL_QTY_TXT"));
                                            QTYNumberTxtView.setText(generalFunc.convertNumberWithRTL(QUANTITY + ""));
                                            leftImgView.setImageResource(R.mipmap.ic_left_arrow_rounded);
                                            leftImgView.setEnabled(true);
                                            selectAllServiceMap.put("fFixedFare", priceTxtView.getText().toString());
                                            selectAllServiceMap.put("QUANTITY", QTYNumberTxtView.getText().toString());

                                            QUANTITYdata = QUANTITY;
                                            QUANTITYPrice = generalFunc.getJsonValueStr("vSymbol", mainCataObject) +
                                                    (QUANTITY * GeneralFunctions.parseDoubleValue(0.0, filterdata)) + "";
                                        }
                                    } else {

                                        generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()),
                                                generalFunc.retrieveLangLBl("Maximum quantity is reached for this service", "LBL_QUANTITY_CLOSED_TXT"));

                                    }
                                } catch (Exception e) {
                                    Logger.d("Exception::", e.toString());

                                }
                            });

                            leftImgView.setOnClickListener(v -> {
                                try {

                                    String filterdataqty = QTYNumberTxtView.getText().toString();
                                    int QUANTITY = Integer.parseInt(filterdataqty);
                                    QUANTITYdata = QUANTITY;
                                    if (QUANTITY > 1) {
                                        QUANTITY = QUANTITY - 1;

                                        String tempdata = generalFunc.getJsonValueStr("fFixedFare_value", mainCataObject);
                                        String filterdata = tempdata.replace(generalFunc.getJsonValueStr("vSymbol", mainCataObject), "");
                                        priceTxtView.setText(generalFunc.getJsonValue("vSymbol", mainCataObject) +
                                                generalFunc.convertNumberWithRTL((QUANTITY * GeneralFunctions.parseDoubleValue(0.0, filterdata)) + ""));
                                        QTYTxtView.setText(generalFunc.retrieveLangLBl("QTY", "LBL_QTY_TXT"));
                                        QTYNumberTxtView.setText(generalFunc.convertNumberWithRTL("" + QUANTITY));
                                        leftImgView.setImageResource(R.mipmap.ic_left_arrow_rounded);
                                        selectAllServiceMap.put("fFixedFare", priceTxtView.getText().toString());
                                        selectAllServiceMap.put("QUANTITY", QTYNumberTxtView.getText().toString());

                                        QUANTITYdata = QUANTITY;
                                        QUANTITYPrice = generalFunc.getJsonValueStr("vSymbol", mainCataObject) +
                                                (QUANTITY * GeneralFunctions.parseDoubleValue(0.0, filterdata)) + "";

                                    } else {
                                        QTYTxtView.setEnabled(false);
                                        leftImgView.setImageResource(R.mipmap.ic_left_disabled_rounded);
                                    }
                                } catch (Exception e) {

                                }
                            });
                            listForViews.add(view);

                            catagoryRadioBtn.setOnClickListener(v -> uberXServiceAreaLayout.performClick());

                            final int finalI = i;
                            final String finalEAllowQty = eAllowQty;
                            uberXServiceAreaLayout.setOnClickListener(v -> {

                                selectedVehicleTypeId = generalFunc.getJsonValueStr("iVehicleTypeId", mainCataObject);
                                selectvVehicleType = generalFunc.getJsonValueStr("vVehicleType", mainCataObject);
                                FareType = generalFunc.getJsonValueStr("eFareType", mainCataObject);


                                if (generalFunc.getJsonValueStr("eFareType", mainCataObject).equalsIgnoreCase(Utils.CabFaretypeFixed)) {
                                    selectvVehiclePrice = generalFunc.getJsonValueStr("fFixedFare", mainCataObject);
                                } else if (generalFunc.getJsonValueStr("eFareType", mainCataObject).equalsIgnoreCase(Utils.CabFaretypeHourly)) {
                                    selectvVehiclePrice = generalFunc.getJsonValue("fPricePerHour", mainCataObject) + "/" + generalFunc.retrieveLangLBl("hour", "LBL_HOUR");

                                }


                                selectedVehiclePosition = finalI;

                                for (int r = 0; r < listForViews.size(); r++) {
                                    ((RadioButton) listForViews.get(r).findViewById(R.id.catagoryRadioBtn)).setChecked(false);
                                    (listForViews.get(r).findViewById(R.id.countingArea)).setVisibility(View.GONE);
                                    (listForViews.get(r).findViewById(R.id.fareDetailArea)).setVisibility(View.GONE);
                                    (listForViews.get(r).findViewById(R.id.minHourArea)).setVisibility(View.GONE);
                                }
                                if (generalFunc.getJsonValueStr("eFareType", mainCataObject).equalsIgnoreCase("Regular")) {
                                    fareDetailArea.setVisibility(View.VISIBLE);

                                } else {
                                    fareDetailArea.setVisibility(View.GONE);
                                }

                                //Min hour addon
                                if (generalFunc.getJsonValueStr("ePriceType", mainCataObject).equalsIgnoreCase("Service") && generalFunc.getJsonValueStr("eFareType", mainCataObject).equalsIgnoreCase("Hourly")) {
                                    minHourArea.setVisibility(View.VISIBLE);
                                } else {
                                    minHourArea.setVisibility(View.GONE);
                                }
                                if (finalEAllowQty.equals("Yes")) {
                                    if (countingArea.getVisibility() == View.VISIBLE) {
                                        countingArea.setVisibility(View.GONE);
                                        catagoryRadioBtn.setChecked(false);
                                    } else {
                                        catagoryRadioBtn.setChecked(true);
                                        countingArea.setVisibility(View.VISIBLE);
                                    }
                                } else {
                                    if (catagoryRadioBtn.isChecked() == false) {
                                        catagoryRadioBtn.setChecked(true);
                                    } else {
                                        catagoryRadioBtn.setChecked(false);
                                    }

                                }
                            });
                            selectAllServiceMap.put("vVehicleType", generalFunc.getJsonValueStr("vVehicleType", mainCataObject));
                            selectAllServiceMap.put("iVehicleTypeId", generalFunc.getJsonValueStr("iVehicleTypeId", mainCataObject));

                            serviceLayout.addView(view);

                            if (i == 0) {
                                uberXServiceAreaLayout.performClick();
                            }
                        }
                    }

                    listOfView.addAll(listForViews);
                } else {
                    if (noDataTxt.getVisibility() == View.GONE) {
                        noDataTxt.setVisibility(View.VISIBLE);
                    }
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message", responseObj)));
                }
            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }

    public void makeTextViewResizable(final TextView tv, final int maxLine, final String expandText, final boolean viewMore, final int color, final int dimension, String vCategoryDesc, String vCategoryTitle) {
        if (tv.getTag() == null) {
            HashMap var7 = new HashMap();
            var7.put("TitleOfTextView", tv.getText().toString());
            var7.put("OrigMaxLines", "" + maxLine);
            tv.setTag(var7);
        }

        ViewTreeObserver var8 = tv.getViewTreeObserver();
        var8.addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {
            public void onGlobalLayout() {
                ViewTreeObserver var1 = tv.getViewTreeObserver();
                var1.removeGlobalOnLayoutListener(this);
                int var2;
                String var3;
                if (maxLine == 0) {
                    var2 = tv.getLayout().getLineEnd(0);
                    var3 = tv.getText().subSequence(0, var2 - expandText.length() + 1) + " " + expandText;
                    tv.setText(var3);
                    tv.setMovementMethod(LinkMovementMethod.getInstance());
                    tv.setText(a(tv.getText().toString(), tv, maxLine, expandText, viewMore, color, dimension, (GeneralFunctions.ResizableTexViewClickListener) null, vCategoryDesc, vCategoryTitle), TextView.BufferType.SPANNABLE);
                } else if (maxLine > 0 && tv.getLineCount() >= maxLine) {
                    var2 = tv.getLayout().getLineEnd(maxLine - 1);
                    var3 = tv.getText().subSequence(0, var2 - expandText.length() + 1) + " " + expandText;
                    tv.setText(var3);
                    tv.setMovementMethod(LinkMovementMethod.getInstance());
                    tv.setText(a(tv.getText().toString(), tv, maxLine, expandText, viewMore, color, dimension, (GeneralFunctions.ResizableTexViewClickListener) null, vCategoryDesc, vCategoryTitle), TextView.BufferType.SPANNABLE);
                } else if (tv.getLineCount() > maxLine) {
                    var2 = tv.getLayout().getLineEnd(tv.getLayout().getLineCount() - 1);
                    var3 = tv.getText().subSequence(0, var2) + " " + expandText;
                    tv.setText(var3);
                    tv.setMovementMethod(LinkMovementMethod.getInstance());
                    tv.setText(a(tv.getText().toString(), tv, var2, expandText, viewMore, color, dimension, (GeneralFunctions.ResizableTexViewClickListener) null, vCategoryDesc, vCategoryTitle), TextView.BufferType.SPANNABLE);
                }

            }
        });
    }

    private SpannableStringBuilder a(String var1, final TextView var2, int var3, String var4, final boolean var5, int var6, int var7, final GeneralFunctions.ResizableTexViewClickListener var8, String vCategoryDesc, String vCategoryTitle) {
        SpannableStringBuilder var9 = new SpannableStringBuilder(var1);
        if (var1.contains(var4)) {
            var9.setSpan(new MyClickableSpan(this, var6, var7) {
                public void onClick(View widget) {
                    if (var8 != null) {
                        var8.onResizableTextViewClick(var5);
                    } else {
                        if (var2.getTag() != null && var2.getTag() instanceof HashMap) {
                            HashMap var2x = (HashMap) var2.getTag();
                            if (var5) {

                                Bundle bn = new Bundle();
                                bn.putString("staticData", vCategoryDesc);
                                bn.putString("vTitle", vCategoryTitle);
                                new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
                            } else {
                                var2.setLayoutParams(var2.getLayoutParams());
                                var2.setText(var2x.get("TitleOfTextView").toString(), TextView.BufferType.SPANNABLE);
                                var2.invalidate();
                                makeTextViewResizable(var2, GeneralFunctions.parseIntegerValue(2, var2x.get("OrigMaxLines").toString()), "...\n+ " + generalFunc.retrieveLangLBl("View More", "LBL_VIEW_MORE_TXT"), true, var6, var7, vCategoryDesc, vCategoryTitle);
                            }
                        }

                    }
                }
            }, var1.indexOf(var4), var1.indexOf(var4) + var4.length(), 0);
        }

        return var9;
    }

    public Context getActContext() {
        return UberXSelectServiceActivity.this;
    }


    public void onClick(View v) {
        try {
            Utils.hideKeyboard(getActContext());
            Bundle bundle = new Bundle();
            bundle.putString("SelectedVehicleTypeId", selectedVehicleTypeId);
            bundle.putString("latitude", getIntent().getStringExtra("latitude"));
            bundle.putString("longitude", getIntent().getStringExtra("longitude"));
            bundle.putString("SelectvVehicleType", selectvVehicleType);
            bundle.putString("SelectvVehiclePrice", selectvVehiclePrice);


            String quantity = "1";
            if (listOfView != null && listOfView.size() > 0 && listOfView.get(selectedVehiclePosition) != null &&
                    listOfView.get(selectedVehiclePosition).findViewById(R.id.QTYNumberTxtView) != null) {
                quantity = ((MTextView) listOfView.get(selectedVehiclePosition).findViewById(R.id.QTYNumberTxtView)).getText().toString();
                QUANTITYPrice = ((MTextView) listOfView.get(selectedVehiclePosition).findViewById(R.id.priceTxtView)).getText().toString();
            }
            if (quantity.equals("0")) {

                quantity = "1";
            }
            if (listOfView != null && ((RelativeLayout) listOfView.get(selectedVehiclePosition).findViewById(R.id.countingArea)).getVisibility() == View.GONE) {
                quantity = "0";
            }
            bundle.putString("Quantity", quantity);


            if (v == backImgView) {
                UberXSelectServiceActivity.super.onBackPressed();
            } else if (v == btn_type2_rideNow) {

                if (!isclick) {
                    isclick = true;
                    bundle.putBoolean("isufx", true);
                    bundle.putString("latitude", getIntent().getStringExtra("latitude"));
                    bundle.putString("longitude", getIntent().getStringExtra("longitude"));
                    bundle.putString("address", getIntent().getStringExtra("address"));
                    bundle.putString("type", Utils.CabReqType_Now);
                    bundle.putString("Quantityprice", QUANTITYPrice);

                    if (!FareType.equals(Utils.CabFaretypeFixed)) {
                        bundle.putBoolean("isWalletShow", true);
                    }

                    if (FareType.equals(Utils.CabFaretypeRegular)) {
                        bundle.putString("SelectvVehiclePrice", "");
                        bundle.putString("iUserAddressId", "");
                        bundle.putString("Quantity", "");
                        bundle.putString("Quantityprice", "");
                        bundle.putString("Sdate", "");
                        bundle.putString("Stime", "");
                        new ActUtils(getActContext()).startActWithData(MainActivity.class, bundle);
                    } else {

                        if (generalFunc.getJsonValueStr("ToTalAddress", obj_userProfile).equalsIgnoreCase("0")) {
                            new ActUtils(getActContext()).startActWithData(AddAddressActivity.class, bundle);
                        } else {
                            new ActUtils(getActContext()).startActWithData(ListAddressActivity.class, bundle);
                        }
                    }

                }

            } else if (v == btn_type2_rideLater) {

                if (!isclick) {
                    isclick = true;
                    bundle.putBoolean("isufx", true);
                    bundle.putString("latitude", getIntent().getStringExtra("latitude"));
                    bundle.putString("longitude", getIntent().getStringExtra("longitude"));
                    bundle.putString("address", getIntent().getStringExtra("address"));
                    bundle.putString("type", Utils.CabReqType_Later);
                    bundle.putString("Quantityprice", QUANTITYPrice);
                    if (!FareType.equals(Utils.CabFaretypeFixed)) {
                        bundle.putBoolean("isWalletShow", true);
                    }


                    if (FareType.equals(Utils.CabFaretypeRegular)) {

                        bundle.putString("SelectvVehiclePrice", "");
                        bundle.putString("iUserAddressId", "");
                        bundle.putString("Quantity", "");
                        bundle.putString("Quantityprice", "");
                        bundle.putString("Sdate", "");
                        bundle.putString("Stime", "");

                        new ActUtils(getActContext()).startActWithData(ScheduleDateSelectActivity.class, bundle);

                    } else {
                        if (generalFunc.getJsonValueStr("ToTalAddress", obj_userProfile).equalsIgnoreCase("0")) {
                            new ActUtils(getActContext()).startActWithData(AddAddressActivity.class, bundle);

                        } else {
                            new ActUtils(getActContext()).startActWithData(ListAddressActivity.class, bundle);
                        }
                    }


                }

            }
        } catch (Exception e) {
            UberXSelectServiceActivity.super.onBackPressed();
        }


    }


}
