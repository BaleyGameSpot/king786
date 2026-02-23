package com.act.deliverAll;

import android.content.Context;
import android.graphics.Color;
import android.os.Bundle;
import android.text.SpannableString;
import android.text.SpannableStringBuilder;
import android.text.Spanned;
import android.text.style.StrikethroughSpan;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.cardview.widget.CardView;

import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;
import com.view.simpleratingbar.SimpleRatingBar;

import org.apache.commons.lang3.StringUtils;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;

public class OrderDetailsActivity extends ParentActivity {

    ImageView backImgView;
    MTextView titleTxt;
    String iOrderId = "";
    LinearLayout farecontainer;
    MTextView resturantAddressTxt, deliveryaddressTxt, resturantAddressHTxt, destAddressHTxt;
    MTextView paidviaTextH;
    MTextView deliverystatusTxt;
    MTextView orderNoHTxt, orderNoVTxt, orderDateVTxt, billTitleTxt;
    LinearLayout cancelArea;
    LinearLayout billDetails;
    LinearLayout deliveryCancelDetails;
    MTextView deliverycanclestatusTxt;
    MTextView oredrstatusTxt;
    ImageView restaurantImgView;
    int size;
    private String vImage;
    private String vAvgRating;
    String vImageDeliveryPref = "";
    CardView viewPreferenceArea;
    MTextView viewPreferenceTxtView;
    MTextView viewPrescTxtView;
    private JSONObject DeliveryPreferences;
    LinearLayout contentView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_order_details);


        size = (int) this.getResources().getDimension(R.dimen._55sdp);
        contentView = (LinearLayout) findViewById(R.id.contentView);
        contentView.setVisibility(View.GONE);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        billDetails = (LinearLayout) findViewById(R.id.billDetails);
        orderNoHTxt = (MTextView) findViewById(R.id.orderNoHTxt);
        orderNoVTxt = (MTextView) findViewById(R.id.orderNoVTxt);
        billTitleTxt = (MTextView) findViewById(R.id.billTitleTxt);
        orderDateVTxt = (MTextView) findViewById(R.id.orderDateVTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        farecontainer = (LinearLayout) findViewById(R.id.fareContainer);
        resturantAddressTxt = (MTextView) findViewById(R.id.resturantAddressTxt);
        resturantAddressHTxt = (MTextView) findViewById(R.id.resturantAddressHTxt);
        deliveryaddressTxt = (MTextView) findViewById(R.id.deliveryaddressTxt);
        destAddressHTxt = (MTextView) findViewById(R.id.destAddressHTxt);
        paidviaTextH = (MTextView) findViewById(R.id.paidviaTextH);
        deliverystatusTxt = (MTextView) findViewById(R.id.deliverystatusTxt);
        deliveryCancelDetails = (LinearLayout) findViewById(R.id.deliveryCancelDetails);
        cancelArea = (LinearLayout) findViewById(R.id.cancelArea);
        deliverycanclestatusTxt = (MTextView) findViewById(R.id.deliverycanclestatusTxt);
        oredrstatusTxt = (MTextView) findViewById(R.id.oredrstatusTxt);
        restaurantImgView = (ImageView) findViewById(R.id.restaurantImgView);
        addToClickHandler(backImgView);
        iOrderId = getIntent().getStringExtra("iOrderId");
        viewPreferenceTxtView = (MTextView) findViewById(R.id.viewPreferenceTxtView);
        viewPreferenceArea = (CardView) findViewById(R.id.viewPreferenceArea);
        viewPrescTxtView = (MTextView) findViewById(R.id.viewPrescTxtView);
        addToClickHandler(viewPrescTxtView);
        addToClickHandler(viewPreferenceArea);
        setLabel();
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        getOrderDetails();

    }

    public void setLabel() {
        titleTxt.setVisibility(View.VISIBLE);
        destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_ADDRESS"));
        billTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BILL_DETAILS"));
        titleTxt.setText(generalFunc.retrieveLangLBl("RECEIPT", "LBL_RECEIPT_HEADER_TXT"));
        viewPreferenceTxtView.setText(generalFunc.retrieveLangLBl("View Preferences", "LBL_VIEW_PREFERENCES"));
    }


    public void getOrderDetails() {
        findViewById(R.id.paymentMainArea).setVisibility(View.GONE);
        findViewById(R.id.detailarea).setVisibility(View.GONE);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetOrderDetailsRestaurant");
        parameters.put("iOrderId", iOrderId);
        parameters.put("UserType", Utils.userType);
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);


            if (responseStringObject != null && !responseStringObject.toString().equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                if (isDataAvail) {
                    findViewById(R.id.paymentMainArea).setVisibility(View.VISIBLE);
                    findViewById(R.id.detailarea).setVisibility(View.VISIBLE);
                    String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);
                    resturantAddressTxt.setText(generalFunc.getJsonValue("vRestuarantLocation", message));
                    vImage = generalFunc.getJsonValue("companyImage", message);
                    vAvgRating = generalFunc.getJsonValue("vAvgRating", message);

                    DeliveryPreferences = generalFunc.getJsonObject("DeliveryPreferences", responseStringObject);
                    vImageDeliveryPref = generalFunc.getJsonValueStr("vImageDeliveryPref", DeliveryPreferences);
                    boolean isPreference = generalFunc.getJsonValueStr("Enable", DeliveryPreferences).equalsIgnoreCase("Yes") ? true : false;
                    viewPreferenceArea.setVisibility(isPreference ? View.VISIBLE : View.GONE);


                    ((SimpleRatingBar) findViewById(R.id.ratingBar)).setRating(generalFunc.parseFloatValue(0, vAvgRating));
                    setImage();
                    deliveryaddressTxt.setText(generalFunc.getJsonValue("DeliveryAddress", message));
                    orderNoHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_NO_TXT"));
                    orderNoVTxt.setText("#" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("vOrderNo", message)));
                    //orderDateVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("tOrderRequestDate_Org", message), Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));
                    orderDateVTxt.setText(generalFunc.getJsonValue("tDisplayDateTime",message));
                    resturantAddressHTxt.setText(StringUtils.capitalize(generalFunc.getJsonValue("vCompany", message)));
                    String LBL_PAID_VIA = generalFunc.retrieveLangLBl("", "LBL_PAID_VIA");
                    String ePaymentOption = generalFunc.getJsonValue("ePaymentOption", message);


                    if (ePaymentOption.equalsIgnoreCase("Cash")) {
                        ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_cash_new);
                        paidviaTextH.setText(LBL_PAID_VIA + " " + generalFunc.retrieveLangLBl("", "LBL_CASH_TXT"));
                    } else if (ePaymentOption.equalsIgnoreCase("Card")) {
                        ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.mipmap.ic_card_new);
                        paidviaTextH.setText(LBL_PAID_VIA + " " + generalFunc.retrieveLangLBl("", "LBL_CARD"));
                    } else if (ePaymentOption.equalsIgnoreCase("Wallet")) {
                        ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_menu_wallet);
                        paidviaTextH.setText(generalFunc.retrieveLangLBl("", "LBL_PAID_VIA_WALLET"));
                    }


                    JSONArray FareDetailsArr = generalFunc.getJsonArray("FareDetailsArr", message);


                    addFareDetailLayout(FareDetailsArr);

                    JSONArray itemListArr = generalFunc.getJsonArray("itemlist", message);

                    if (billDetails.getChildCount() > 0) {
                        billDetails.removeAllViewsInLayout();
                    }
                    addItemDetailLayout(itemListArr);

                    deliverystatusTxt.setText(GeneralFunctions.fromHtml(generalFunc.getJsonValueStr("vStatusNew", responseStringObject)));

                    if (generalFunc.getJsonValue("iStatusCode", message).equalsIgnoreCase("6") && generalFunc.getJsonValue("ePaid", message).equals("Yes")) {
                        deliverystatusTxt.setVisibility(View.VISIBLE);
                        deliverystatusTxt.setText(GeneralFunctions.fromHtml(generalFunc.getJsonValue("OrderStatusValue", message)));
                        findViewById(R.id.PayTypeArea).setVisibility(View.VISIBLE);

                    } else if (generalFunc.getJsonValue("iStatusCode", message).equalsIgnoreCase("8")) {
                        deliveryCancelDetails.setVisibility(View.GONE);
                        deliverycanclestatusTxt.setText(generalFunc.getJsonValue("OrderStatustext", message));

                        if (!generalFunc.getJsonValue("CancelOrderMessage", message).equals("") && generalFunc.getJsonValue("CancelOrderMessage", message) != null) {

                            deliveryCancelDetails.setVisibility(View.VISIBLE);
                            deliverycanclestatusTxt.setVisibility(View.GONE);
                            oredrstatusTxt.setVisibility(View.VISIBLE);
                            oredrstatusTxt.setText(generalFunc.getJsonValue("CancelOrderMessage", message));
                        }
                    } else if (generalFunc.getJsonValue("iStatusCode", message).equalsIgnoreCase("7")) {
                        deliveryCancelDetails.setVisibility(View.VISIBLE);
                        cancelArea.setVisibility(View.GONE);
                        if (!generalFunc.getJsonValue("CancelOrderMessage", message).equals("") && generalFunc.getJsonValue("CancelOrderMessage", message) != null) {
                            oredrstatusTxt.setVisibility(View.VISIBLE);
                            oredrstatusTxt.setText(generalFunc.getJsonValue("CancelOrderMessage", message));
                        }

                    } else {
                        findViewById(R.id.paymentMainArea).setVisibility(View.GONE);
                    }

                    deliverystatusTxt.setText(generalFunc.getJsonValue("vStatusNew", message));
                    contentView.setVisibility(View.VISIBLE);
                }
            }
        });
    }

    private void setImage() {
        if (Utils.checkText(vImage)) {
            new LoadImage.builder(LoadImage.bind(vImage), restaurantImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
        }
    }


    private void addItemDetailLayout(JSONArray jobjArray) {


        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String extrapayment = "";
                String eItemAvailable = "";
                if (jobject.has("eExtraPayment")) {
                    extrapayment = jobject.getString("eExtraPayment");
                }
                if (jobject.has("eItemAvailable")) {
                    eItemAvailable = jobject.getString("eItemAvailable");
                }


                additemDetailRow(jobject.getString("vImage"), jobject.getString("MenuItem"), jobject.getString("SubTitle"), jobject.getString("fTotPrice"), /*" x " + */"" + jobject.get("iQty"), jobject.getString("TotalDiscountPrice"), extrapayment, eItemAvailable);
            } catch (Exception e) {
                Logger.d("JsonException", "::" + e.toString());
            }
        }

    }

    private void additemDetailRow(String itemImage, String menuitemName, String subMenuName, String itemPrice, String qty, String discountprice, String eExtraPayment, String eItemAvailable) {
        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View view = inflater.inflate(R.layout.item_select_bill_design, null);

        MTextView billItems = (MTextView) view.findViewById(R.id.billItems);
        MTextView itemNoteTxt = (MTextView) view.findViewById(R.id.itemNoteTxt);
        MTextView billItemsQty = (MTextView) view.findViewById(R.id.billItemsQty);
        ImageView imageFoodType = (ImageView) view.findViewById(R.id.imageFoodType);
        CardView foodImageArea = (CardView) view.findViewById(R.id.foodImageArea);
        MTextView strikeoutbillAmount = (MTextView) view.findViewById(R.id.strikeoutbillAmount);
        MTextView serviceTypeNameTxtView = (MTextView) view.findViewById(R.id.serviceTypeNameTxtView);
        final MTextView billAmount = (MTextView) view.findViewById(R.id.billAmount);
        final MTextView billExtraAmount = (MTextView) view.findViewById(R.id.billExtraAmount);
        foodImageArea.setVisibility(View.VISIBLE);

        new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(getActContext(), itemImage, size, size)), imageFoodType).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

        billAmount.setText(generalFunc.convertNumberWithRTL(itemPrice));
        billItemsQty.setText(generalFunc.convertNumberWithRTL(qty));

        billItems.setText(menuitemName);
        if (!subMenuName.equalsIgnoreCase("")) {
            serviceTypeNameTxtView.setVisibility(View.VISIBLE);
            serviceTypeNameTxtView.setText(subMenuName);
        } else {
            serviceTypeNameTxtView.setVisibility(View.GONE);
        }
        itemNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ITEM_NOT_AVAILABLE"));
        if (eItemAvailable != null && eItemAvailable.equalsIgnoreCase("No")) {
            SpannableStringBuilder spanBuilder = new SpannableStringBuilder();
            itemNoteTxt.setVisibility(View.VISIBLE);
            SpannableString origSpan = new SpannableString(billItems.getText());

            origSpan.setSpan(new StrikethroughSpan(), 0, billItems.getText().length(), Spanned.SPAN_INCLUSIVE_EXCLUSIVE);

            spanBuilder.append(origSpan);

            billItems.setText(spanBuilder);
        }

        if (eExtraPayment != null && eExtraPayment.equalsIgnoreCase("No")) {
            billExtraAmount.setText(generalFunc.retrieveLangLBl("", "LBL_PAYMENT_NOT_REQUIRED"));
            billExtraAmount.setVisibility(View.VISIBLE);
            billAmount.setVisibility(View.GONE);
        } else {

            if (discountprice != null && !discountprice.equals("")) {
                SpannableStringBuilder spanBuilder = new SpannableStringBuilder();

                SpannableString origSpan = new SpannableString(billAmount.getText());

                origSpan.setSpan(new StrikethroughSpan(), 0, billAmount.getText().length(), Spanned.SPAN_INCLUSIVE_EXCLUSIVE);

                spanBuilder.append(origSpan);

                strikeoutbillAmount.setVisibility(View.VISIBLE);
                strikeoutbillAmount.setText(spanBuilder);
                billAmount.setText(discountprice);
            } else {
                strikeoutbillAmount.setVisibility(View.GONE);
                billAmount.setTextColor(getResources().getColor(R.color.appThemeColor_1));
                billAmount.setPaintFlags(billAmount.getPaintFlags());
            }
        }


        billDetails.addView(view);
    }


    private void addFareDetailLayout(JSONArray jobjArray) {

        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String data = jobject.names().getString(0);
                addFareDetailRow(data, jobject.get(data).toString(), (jobjArray.length() - 1) == i ? true : false);
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

    }


    private void addFareDetailRow(String row_name, String row_value, boolean isLast) {
        View convertView;
        if (row_name.equalsIgnoreCase("eDisplaySeperator")) {
            convertView = new View(getActContext());
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, Utils.dipToPixels(getActContext(), 1));
            params.setMargins(0, 0, 0, (int) getResources().getDimension(R.dimen._5sdp));
            convertView.setBackgroundColor(Color.parseColor("#dedede"));
            convertView.setLayoutParams(params);
        } else {
            LayoutInflater infalInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = infalInflater.inflate(R.layout.design_fare_deatil_row, null);

            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            params.setMargins(0, (int) getResources().getDimension(R.dimen._10sdp), 0, isLast ? (int) getResources().getDimension(R.dimen._10sdp) : 0);
            convertView.setLayoutParams(params);

            MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
            MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);

            titleHTxt.setText(generalFunc.convertNumberWithRTL(row_name));
            titleVTxt.setText(generalFunc.convertNumberWithRTL(row_value));

            if (isLast) {
                // CALCULATE individual fare & show
                titleHTxt.setTextColor(getResources().getColor(R.color.black));
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);

                titleHTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTypeface(SystemFont.FontStyle.SEMI_BOLD.font);
                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
                titleVTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));

            }

        }

        if (convertView != null)
            farecontainer.addView(convertView);
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.viewPreferenceArea) {
            Bundle bundle = new Bundle();
            bundle.putString("DeliveryPreferences", DeliveryPreferences.toString());
            new ActUtils(getActContext()).startActWithData(UserPrefrenceActivity.class, bundle);
        }
    }


    public Context getActContext() {
        return OrderDetailsActivity.this;
    }


}
