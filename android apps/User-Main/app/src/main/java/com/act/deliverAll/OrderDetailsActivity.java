package com.act.deliverAll;

import static com.buddyverse.main.R.id.ratingBar;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.InputFilter;
import android.text.InputType;
import android.text.SpannableString;
import android.text.SpannableStringBuilder;
import android.text.Spanned;
import android.text.method.ScrollingMovementMethod;
import android.text.style.StrikethroughSpan;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RatingBar;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.cardview.widget.CardView;
import androidx.core.content.ContextCompat;
import androidx.core.view.GravityCompat;
import androidx.core.widget.NestedScrollView;

import com.act.CardPaymentActivity;
import com.act.HelpMainCategory23Pro;
import com.act.PrescriptionActivity;
import com.act.QuickPaymentActivity;
import com.act.UberXHomeActivity;
import com.act.UserPrefrenceActivity;
import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.general.files.ActUtils;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.realmModel.Cart;
import com.realmModel.Options;
import com.realmModel.Topping;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.view.editBox.MaterialEditText;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;

import io.realm.Realm;
import io.realm.RealmList;
import io.realm.RealmResults;

public class OrderDetailsActivity extends ParentActivity {

    LinearLayout billDetails;


    ImageView backImgView;
    ImageView receiptImgView;
    MTextView titleTxt;
    String iOrderId = "";
    String iServiceId = "";
    String vAvgRating = "";

    MTextView billTitleTxt;
    MTextView resturantAddressTxt, deliveryaddressTxt, resturantAddressHTxt, destAddressHTxt;
    MTextView orderNoHTxt, orderNoVTxt, orderDateVTxt;
    MTextView paidviaTextH;
    MTextView deliverystatusTxt;
    LinearLayout chargeDetailArea;
    ImageView helpTxt;
    boolean ePaid = false;
    String ePaymentOption = "";
    MButton btn_type2;
    String iCompanyId = "";
    String vCompany = "";
    String toppingId = "";
    String iMenuItemId = "";
    String optionId = "";
    ArrayList<HashMap<String, String>> cartList;
    String currencySymbol = "";
    String fMinOrderValue = "";
    RealmList<Topping> realmToppingList = new RealmList<>();
    RealmList<Options> realmOptionsList = new RealmList<>();
    String isOption = "No";
    String isTooping = "No";
    LinearLayout deliveryCancelDetails;
    MTextView deliverycanclestatusTxt;
    MTextView oredrstatusTxt;
    LinearLayout cancelArea;
    ProgressBar mProgressBar;
    View contentView;
    ArrayList<HashMap<String, String>> imageList = new ArrayList<>();
    int size;
    LinearLayout sourceLocCardArea;
    MTextView eTakeAwayHTxt;
    MTextView eTakeAwayOrderTxt;
    MTextView takeAwayPickedUpaddressTxt;
    MTextView takawayOrderStatusTxt;
    LinearLayout eTakeAwayCardArea, tipCardArea;
    LinearLayout takawayOrderStatusArea;
    String eTakeAway = "";
    String eTakeAwayPickedUpNote = "";
    String vImageDeliveryPref = "";
    LinearLayout viewPreferenceArea;
    MTextView viewPreferenceTxtView;
    MTextView viewPrescTxtView;
    MTextView ufxratingDriverHTxt;
    SimpleRatingBar ufxratingBar;
    MButton btn_type_rate;
    int rateBtnId;
    LinearLayout tipAmountArea1, tipAmountArea2, tipAmountArea3, tipAmountAreaOther;
    ImageView closeImg1, closeImg2, closeImg3, closeImgOther;
    MTextView tipAmountText1, tipAmountText2, tipAmountText3, tipAmountOtherText;
    MTextView tipTitleText;
    ImageView iv_tip_help;
    MTextView tipDescTitleText;
    MTextView tipDescHintTitleText;
    EditText tipAmountBox;
    MTextView errorTxt;
    LinearLayout addTipArea;
    LinearLayout amountArea;
    FrameLayout btnArea;
    SelectableRoundedImageView tipGivenBtn;
    AlertDialog giveTipAlertDialog;
    String tipAmount = "";
    String giveTip = "";
    MButton giveTipArea;
    String eBuyAnyService = "";
    String GenieOrderType = "";
    //manage Proof
    CardView photoArea;
    MTextView proofImgHTxt;
    ImageView iv_proof_img;
    String vIdProofImageUploaded;
    String vIdProofImage;
    MTextView cancelReasonTxt;
    boolean isRestart;
    boolean isCartNull;
    private SelectableRoundedImageView restaurantImgView;
    private ImageView runnerImgView;
    private MaterialEditText commentBox;
    private JSONObject DeliveryPreferences;
    private boolean isPercentage;
    private Dialog dialog_sucess;
    private JSONArray categoryArray;
    ArrayList<JSONArray> list_categoryArray = null;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_order_details);

        if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
            contentView = findViewById(R.id.contentView);
            contentView.setVisibility(View.GONE);
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"), true);
            return;
        }

        isRestart = getIntent().getBooleanExtra("isRestart", false);

        iOrderId = getIntent().getStringExtra("iOrderId");
        iServiceId = getIntent().getStringExtra("iServiceId");

        if (!Utils.checkText(iServiceId) || !Utils.checkText(iOrderId)) {
            contentView = findViewById(R.id.contentView);
            contentView.setVisibility(View.GONE);
            generalFunc.showError(true);
            return;
        }
        initViews();

        generalFunc.storeData(Utils.iServiceId_KEY, iServiceId);
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            DeliverAllServiceCategory.getInstance().getLanguageLabelServiceWise(iServiceId, (allData, dataDic) -> {

                HashMap<String, String> storeData = new HashMap<>();
                storeData.put(Utils.languageLabelsKey, dataDic);
                storeData.put(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", allData));
                storeData.put(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", allData));
                storeData.put(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", allData));
                generalFunc.storeData(storeData);
                GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
                Utils.setAppLocal(MyApp.getInstance().getCurrentAct());

                getOrderDetails();
                tipUI();
                setLabel();
            });
        }, 77);
    }

    private void initViews() {
        size = (int) this.getResources().getDimension(R.dimen._55sdp);

        cartList = new ArrayList<>();
        photoArea = (CardView) findViewById(R.id.photoArea);
        proofImgHTxt = (MTextView) findViewById(R.id.proofImgHTxt);
        iv_proof_img = (ImageView) findViewById(R.id.iv_proof_img);
        billDetails = (LinearLayout) findViewById(R.id.billDetails);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        cancelReasonTxt = (MTextView) findViewById(R.id.cancelReasonTxt);
        receiptImgView = (ImageView) findViewById(R.id.receiptImgView);

        backImgView = (ImageView) findViewById(R.id.backImgView);
        billTitleTxt = (MTextView) findViewById(R.id.billTitleTxt);
        resturantAddressTxt = (MTextView) findViewById(R.id.resturantAddressTxt);
        resturantAddressHTxt = (MTextView) findViewById(R.id.resturantAddressHTxt);
        deliveryaddressTxt = (MTextView) findViewById(R.id.deliveryaddressTxt);

        eTakeAwayOrderTxt = (MTextView) findViewById(R.id.eTakeAwayOrderTxt);
        eTakeAwayHTxt = (MTextView) findViewById(R.id.eTakeAwayHTxt);
        takeAwayPickedUpaddressTxt = (MTextView) findViewById(R.id.takeAwayPickedUpaddressTxt);
        takawayOrderStatusTxt = (MTextView) findViewById(R.id.takawayOrderStatusTxt);
        eTakeAwayCardArea = (LinearLayout) findViewById(R.id.eTakeAwayCardArea);
        tipCardArea = (LinearLayout) findViewById(R.id.tipCardArea);
        takawayOrderStatusArea = (LinearLayout) findViewById(R.id.takawayOrderStatusArea);

        sourceLocCardArea = (LinearLayout) findViewById(R.id.sourceLocCardArea);
        orderNoHTxt = (MTextView) findViewById(R.id.orderNoHTxt);
        orderNoVTxt = (MTextView) findViewById(R.id.orderNoVTxt);
        orderDateVTxt = (MTextView) findViewById(R.id.orderDateVTxt);

        destAddressHTxt = (MTextView) findViewById(R.id.destAddressHTxt);
        paidviaTextH = (MTextView) findViewById(R.id.paidviaTextH);
        deliverystatusTxt = (MTextView) findViewById(R.id.deliverystatusTxt);
        chargeDetailArea = (LinearLayout) findViewById(R.id.chargeDetailArea);
        helpTxt = (ImageView) findViewById(R.id.helpTxt);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        deliveryCancelDetails = (LinearLayout) findViewById(R.id.deliveryCancelDetails);
        deliverycanclestatusTxt = (MTextView) findViewById(R.id.deliverycanclestatusTxt);
        restaurantImgView = (SelectableRoundedImageView) findViewById(R.id.restaurantImgView);
        runnerImgView = (ImageView) findViewById(R.id.runnerImgView);
        oredrstatusTxt = (MTextView) findViewById(R.id.oredrstatusTxt);
        cancelArea = (LinearLayout) findViewById(R.id.cancelArea);
        mProgressBar = (ProgressBar) findViewById(R.id.loading);
        contentView = findViewById(R.id.contentView);
        contentView.setVisibility(View.GONE);

        viewPreferenceTxtView = (MTextView) findViewById(R.id.viewPreferenceTxtView);
        viewPreferenceArea = (LinearLayout) findViewById(R.id.viewPreferenceArea);
        viewPrescTxtView = (MTextView) findViewById(R.id.viewPrescTxtView);
        addToClickHandler(viewPrescTxtView);

        ufxratingDriverHTxt = (MTextView) findViewById(R.id.ufxratingDriverHTxt);
        ufxratingBar = (SimpleRatingBar) findViewById(R.id.ufxratingBar);
        commentBox = (MaterialEditText) findViewById(R.id.commentBox);
        commentBox.setInputType(InputType.TYPE_TEXT_FLAG_IME_MULTI_LINE);
        commentBox.setSingleLine(false);
        commentBox.setHideUnderline(true);
        commentBox.setGravity(GravityCompat.START | Gravity.TOP);
        commentBox.setLines(5);

        btn_type_rate = ((MaterialRippleLayout) findViewById(R.id.btn_type_rate)).getChildView();
        btn_type_rate.setText(generalFunc.retrieveLangLBl("Rate", "LBL_RATE_DRIVER_TXT"));
        rateBtnId = Utils.generateViewId();
        btn_type_rate.setId(rateBtnId);
        addToClickHandler(btn_type_rate);
        commentBox.setOnTouchListener((v, event) -> {
            ((NestedScrollView) findViewById(R.id.scrollContainer)).requestDisallowInterceptTouchEvent(true);
            return false;
        });

        commentBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_WRITE_COMMENT_HINT_TXT"));
        ufxratingDriverHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOW_WAS_YOUR_DELIVERY"));


        btn_type2.setId(Utils.generateViewId());
        addToClickHandler(viewPreferenceArea);
        addToClickHandler(btn_type2);
        addToClickHandler(backImgView);
        addToClickHandler(receiptImgView);
        addToClickHandler(helpTxt);

        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    private void tipUI() {
        tipCardArea = (LinearLayout) findViewById(R.id.tipCardArea);
        tipAmountOtherText = (MTextView) findViewById(R.id.tipAmountTextOther);
        tipAmountAreaOther = (LinearLayout) findViewById(R.id.tipAmountAreaOther);
        closeImgOther = (ImageView) findViewById(R.id.closeImgOther);

        tipAmountText1 = (MTextView) findViewById(R.id.tipAmountText1);
        tipAmountArea1 = (LinearLayout) findViewById(R.id.tipAmountArea1);
        closeImg1 = (ImageView) findViewById(R.id.closeImg1);

        tipAmountText2 = (MTextView) findViewById(R.id.tipAmountText2);
        tipAmountArea2 = (LinearLayout) findViewById(R.id.tipAmountArea2);
        closeImg2 = (ImageView) findViewById(R.id.closeImg2);

        tipAmountText3 = (MTextView) findViewById(R.id.tipAmountText3);
        tipAmountArea3 = (LinearLayout) findViewById(R.id.tipAmountArea3);
        closeImg3 = (ImageView) findViewById(R.id.closeImg3);


        addToClickHandler(tipAmountArea1);
        addToClickHandler(closeImg1);
        addToClickHandler(tipAmountArea2);
        addToClickHandler(closeImg2);
        addToClickHandler(tipAmountArea3);
        addToClickHandler(closeImg3);
        addToClickHandler(tipAmountAreaOther);
        addToClickHandler(closeImgOther);


        tipTitleText = (MTextView) findViewById(R.id.tipTitleText);
        iv_tip_help = (ImageView) findViewById(R.id.iv_tip_help);
        addToClickHandler(iv_tip_help);

        tipDescTitleText = (MTextView) findViewById(R.id.tipDescTitleText);
        tipDescHintTitleText = (MTextView) findViewById(R.id.tipDescHintTitleText);

        tipAmountBox = (EditText) findViewById(R.id.tipAmountBox);
        errorTxt = (MTextView) findViewById(R.id.errorTxt);
        addTipArea = (LinearLayout) findViewById(R.id.addTipArea);
        amountArea = (LinearLayout) findViewById(R.id.amountArea);
        btnArea = (FrameLayout) findViewById(R.id.tipBtnArea);
        giveTipArea = ((MaterialRippleLayout) findViewById(R.id.giveTipArea)).getChildView();
        giveTipArea.setId(Utils.generateViewId());
        addToClickHandler(giveTipArea);
//        tipGivenBtn = ((MaterialRippleLayout) findViewById(R.id.tipGivenBtn)).getChildView();
        tipGivenBtn = (SelectableRoundedImageView) findViewById(R.id.tipGivenBtn);
//        giveTipId = Utils.generateViewId();
//        tipGivenBtn.setId(giveTipId);
        addToClickHandler(tipGivenBtn);

        new CreateRoundedView(getResources().getColor(R.color.appThemeColor_1), Utils.dipToPixels(getActContext(), getResources().getDimension(R.dimen._30sdp)), 5, getResources().getColor(R.color.gray_holo_dark), tipGivenBtn);
//        tipGivenBtn.setColorFilter(getResources().getColor(R.color.appThemeColor_TXT_1));

        tipAmountBox.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        tipAmountBox.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});

        tipTitleText.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_TIP_TITLE_TXT"));
        tipDescTitleText.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_TIP_DESCRIPTION_TXT"));
        tipDescHintTitleText.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_TIP_HOW_WORKS_TXT"));
        tipAmountBox.setHint(generalFunc.retrieveLangLBl("", "LBL_TIP_AMOUNT_ENTER_TITLE"));
//        tipGivenBtn.setText(generalFunc.retrieveLangLBl("","LBL_BTN_SUBMIT_TXT"));


        addToClickHandler(tipDescHintTitleText);
        tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));

        String CurrencySymbol = generalFunc.getJsonValueStr("CurrencySymbol", obj_userProfile);
        String DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL = generalFunc.getJsonValueStr("DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL", obj_userProfile);
        String tipAMount1 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TIP_AMOUNT_1", obj_userProfile));
        String tipAMount2 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TIP_AMOUNT_2", obj_userProfile));
        String tipAMount3 = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TIP_AMOUNT_3", obj_userProfile));
        isPercentage = DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL.equalsIgnoreCase("Percentage");
        boolean eReverseSymbolEnable = generalFunc.retrieveValue("eReverseSymbolEnable").equalsIgnoreCase("Yes");
        tipAmountText1.setTag(isPercentage ? 1 : tipAMount1);
        tipAmountText1.setText(isPercentage ? tipAMount1 : eReverseSymbolEnable ? tipAMount1 + " " + CurrencySymbol : CurrencySymbol + tipAMount1);
        tipAmountText2.setTag(isPercentage ? 2 : tipAMount2);
        tipAmountText2.setText(isPercentage ? tipAMount2 : eReverseSymbolEnable ? tipAMount2 + " " + CurrencySymbol : CurrencySymbol + tipAMount2);
        tipAmountText3.setTag(isPercentage ? 3 : tipAMount3);
        tipAmountText3.setText(isPercentage ? tipAMount3 : eReverseSymbolEnable ? tipAMount3 + " " + CurrencySymbol : CurrencySymbol + tipAMount3);
        tipAmountOtherText.setTag(isPercentage ? 4 : "");

    }

    public void setLabel() {
        giveTipArea.setText(generalFunc.retrieveLangLBl("", "LBL_GIVE_TIP"));
        billTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BILL_DETAILS"));
        titleTxt.setText(generalFunc.retrieveLangLBl("RECEIPT", "LBL_RECEIPT_HEADER_TXT"));
        destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_ADDRESS"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_REORDER"));
        viewPrescTxtView.setText(generalFunc.retrieveLangLBl("View Prescription", "LBL_VIEW_PRESCRIPTION"));
        viewPreferenceTxtView.setText(generalFunc.retrieveLangLBl("View Preferences", "LBL_VIEW_PREFERENCES"));
        eTakeAwayOrderTxt.setText(generalFunc.retrieveLangLBl("Take Away Order", "LBL_TAKE_WAY_ORDER"));
        eTakeAwayHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RESTAURANT_ADDRESS"));
        proofImgHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_IDENTIFICATION"));
        ((MTextView) findViewById(R.id.chargesHTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));


    }

    public void getOrderDetails() {

        mProgressBar.setVisibility(View.VISIBLE);
        findViewById(R.id.paymentMainArea).setVisibility(View.GONE);
        contentView.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetOrderDetailsRestaurant");
        parameters.put("iOrderId", iOrderId);
        parameters.put("UserType", Utils.userType);
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            mProgressBar.setVisibility(View.GONE);
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    JSONObject message = generalFunc.getJsonObject(Utils.message_str, responseString);
                    DeliveryPreferences = generalFunc.getJsonObject("DeliveryPreferences", responseString);

                    vIdProofImageUploaded = generalFunc.getJsonValueStr("vIdProofImageUploaded", message);
                    vIdProofImage = generalFunc.getJsonValueStr("vIdProofImage", message);
                    iCompanyId = generalFunc.getJsonValueStr("iCompanyId", message);
                    currencySymbol = generalFunc.getJsonValueStr("currencySymbol", message);
                    fMinOrderValue = generalFunc.getJsonValueStr("fMinOrderValue", message);
                    vCompany = generalFunc.getJsonValueStr("vCompany", message);
                    iOrderId = generalFunc.getJsonValueStr("iOrderId", message);
                    iServiceId = generalFunc.getJsonValueStr("iServiceId", message);
                    vAvgRating = generalFunc.getJsonValueStr("vAvgRating", message);
                    vImageDeliveryPref = generalFunc.getJsonValueStr("vImageDeliveryPref", DeliveryPreferences);
                    eTakeAway = generalFunc.getJsonValueStr("eTakeAway", message);
                    eTakeAwayPickedUpNote = generalFunc.getJsonValueStr("eTakeAwayPickedUpNote", message);
                    giveTip = generalFunc.getJsonValueStr("giveTip", message);
                    tipCardArea.setVisibility(giveTip.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);
                    boolean isPreference = generalFunc.getJsonValueStr("Enable", DeliveryPreferences).equalsIgnoreCase("Yes");
                    boolean iseTakeAway = eTakeAway.equalsIgnoreCase("Yes");

                    viewPreferenceArea.setVisibility(isPreference ? View.VISIBLE : View.GONE);
                    eTakeAwayOrderTxt.setVisibility(iseTakeAway ? View.VISIBLE : View.GONE);
                    eTakeAwayCardArea.setVisibility(iseTakeAway ? View.VISIBLE : View.GONE);
                    sourceLocCardArea.setVisibility(iseTakeAway ? View.GONE : View.VISIBLE);
                    takawayOrderStatusArea.setVisibility(Utils.checkText(eTakeAwayPickedUpNote) ? View.VISIBLE : View.GONE);

                    if (vAvgRating.isEmpty()) vAvgRating = "0.0";
                    ((SimpleRatingBar) findViewById(R.id.ratingBar)).setRating(Float.parseFloat(vAvgRating));
                    GenieOrderType = generalFunc.getJsonValueStr("GenieOrderType", message);
                    setImage(generalFunc.getJsonValueStr("companyImage", message));


                    if (generalFunc.getJsonValueStr("DisplayReorder", message).equalsIgnoreCase("Yes")) {
                        Logger.d("BTN_DISPLAY", "Make VISIBLE::");
                        findViewById(R.id.btnArea).setVisibility(View.VISIBLE);
                        helpTxt.setVisibility(View.VISIBLE);
                        receiptImgView.setVisibility(View.VISIBLE);
                    } else {
                        findViewById(R.id.btnArea).setVisibility(View.GONE);
                        helpTxt.setVisibility(View.GONE);
                        receiptImgView.setVisibility(View.GONE);
                    }


                    resturantAddressTxt.setText(generalFunc.getJsonValueStr("vRestuarantLocation", message));
                    deliveryaddressTxt.setText(generalFunc.getJsonValueStr("DeliveryAddress", message));
                    takeAwayPickedUpaddressTxt.setText(generalFunc.getJsonValueStr("vRestuarantLocation", message));
                    takawayOrderStatusTxt.setText(eTakeAwayPickedUpNote);

                    orderNoHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER_NO_TXT"));
                    orderNoVTxt.setText("#" + generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vOrderNo", message)));
                    //orderDateVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValueStr("tOrderRequestDate_Org", message), Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc))));
                    orderDateVTxt.setText(generalFunc.getJsonValueStr("tDisplayDateTime", message));


                    resturantAddressHTxt.setText(generalFunc.getJsonValueStr("vCompany", message));
                    JSONArray itemListArr = null;
                    itemListArr = generalFunc.getJsonArray("itemlist", message);
                    if (billDetails.getChildCount() > 0) {
                        billDetails.removeAllViewsInLayout();
                    }
                    addItemDetailLayout(itemListArr);

                    eBuyAnyService = generalFunc.getJsonValueStr("eBuyAnyService", message);


                    JSONArray PrescriptionImages = generalFunc.getJsonArray("PrescriptionImages", message);

                    if (PrescriptionImages != null) {
                        findViewById(R.id.viewPrescriptionArea).setVisibility(View.VISIBLE);
                        addToClickHandler(findViewById(R.id.viewPrescriptionArea));

                        for (int i = 0; i < PrescriptionImages.length(); i++) {
                            HashMap<String, String> map = new HashMap<>();
                            map.put("vImage", generalFunc.getJsonValue(PrescriptionImages, i).toString());
                            imageList.add(map);

                        }

                    }

                    String LBL_PAID_VIA = generalFunc.retrieveLangLBl("", "LBL_PAID_VIA");
                    if (generalFunc.getJsonValueStr("ePaymentOption", message).equalsIgnoreCase("Cash")) {
                        ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_cash_payment);
                        paidviaTextH.setText(LBL_PAID_VIA + " " + generalFunc.retrieveLangLBl("", "LBL_CASH_TXT"));
                    } else if (generalFunc.getJsonValueStr("ePaymentOption", message).equalsIgnoreCase("Card")) {

                        ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.mipmap.ic_card_new);
                        paidviaTextH.setText(LBL_PAID_VIA + " " + generalFunc.retrieveLangLBl("", "LBL_CARD"));

                    } else if (generalFunc.getJsonValueStr("ePaymentOption", message).equalsIgnoreCase("Wallet")) {

                        ((ImageView) findViewById(R.id.paymentTypeImgeView)).setImageResource(R.drawable.ic_menu_wallet);
                        paidviaTextH.setText(generalFunc.retrieveLangLBl("", "LBL_PAID_VIA_WALLET"));

                    }


                    JSONArray FareDetailsArr = null;
                    FareDetailsArr = generalFunc.getJsonArray("FareDetailsArr", message);

                    if (generalFunc.getJsonValueStr("iStatusCode", message).equalsIgnoreCase("6") && generalFunc.getJsonValueStr("ePaid", message).equals("Yes")) {
                        ePaid = true;
                        ePaymentOption = generalFunc.getJsonValueStr("ePaymentOption", message);
                        deliverystatusTxt.setVisibility(View.VISIBLE);
                        deliverystatusTxt.setText(GeneralFunctions.fromHtml(generalFunc.getJsonValueStr("vStatusNew", message)));
                        findViewById(R.id.PayTypeArea).setVisibility(View.VISIBLE);

                        JSONArray OrderDataArray = generalFunc.getJsonArray("DataReorder", message);

                        if (OrderDataArray != null) {
                            list_categoryArray = new ArrayList<JSONArray>();

                            cartList.clear();
                            for (int i = 0; i < OrderDataArray.length(); i++) {
                                optionId = "";
                                toppingId = "";
                                isOption = "No";
                                isTooping = "No";
                                JSONObject orderObj = generalFunc.getJsonObject(OrderDataArray, i);
                                HashMap<String, String> map = new HashMap<>();
                                map.put("Qty", generalFunc.getJsonValueStr("iQty", orderObj));
                                map.put("vItemType", generalFunc.getJsonValueStr("MenuItem", orderObj));
                                map.put("vImage", generalFunc.getJsonValueStr("vImage", orderObj));
                                map.put("fDiscountPrice", generalFunc.getJsonValueStr("fPrice", orderObj));

                                map.put("eFoodType", generalFunc.getJsonValueStr("eFoodType", orderObj));
                                map.put("iFoodMenuId", generalFunc.getJsonValueStr("iFoodMenuId", orderObj));
                                map.put("iCompanyId", iCompanyId);
                                map.put("vCompany", vCompany);
                                optionId = generalFunc.getJsonValueStr("vOptionId", orderObj);
                                iMenuItemId = generalFunc.getJsonValueStr("iMenuItemId", orderObj);
                                map.put("iMenuItemId", iMenuItemId);
                                map.put("ispriceshow", generalFunc.getJsonValue("ispriceshow", responseString));

                                JSONArray OptionsArr = generalFunc.getJsonArray("OptionsArr", orderObj);
                                if (OptionsArr != null && OptionsArr.length() > 0) {
                                    for (int j = 0; j < OptionsArr.length(); j++) {
                                        JSONObject toppingObject = generalFunc.getJsonObject(OptionsArr, j);
                                        if (optionId.equals("")) {
                                            optionId = generalFunc.getJsonValueStr("vOptionId", toppingObject);
                                        } else {
                                            optionId = optionId + "," + generalFunc.getJsonValueStr("vOptionId", toppingObject);
                                        }
                                    }
                                }
                                map.put("optionId", optionId);


                                JSONArray toppingArray = generalFunc.getJsonArray("AddOnItemArr", orderObj);

                                if (toppingArray != null && toppingArray.length() > 0) {
                                    for (int j = 0; j < toppingArray.length(); j++) {

                                        JSONObject toppingObject = generalFunc.getJsonObject(toppingArray, j);

                                        if (toppingId.equals("")) {
                                            toppingId = generalFunc.getJsonValueStr("vAddonId", toppingObject);
                                        } else {
                                            toppingId = toppingId + "," + generalFunc.getJsonValueStr("vAddonId", toppingObject);
                                        }


                                    }
                                }

                                map.put("iToppingId", toppingId);


                                categoryArray = generalFunc.getJsonArray("Category", generalFunc.getJsonObject("MenuItemOptionToppingArr", generalFunc.getJsonObject("menu_items", generalFunc.getJsonObject(OrderDataArray, i))));
                                if (categoryArray != null) {
                                    isOption = "Yes";
                                    isTooping = "Yes";
                                    map.put("categoryArray", categoryArray.toString());
                                }
                                map.put("isOption", isOption);
                                map.put("isTopping", isTooping);
                                cartList.add(map);
                                JSONArray optionArray = generalFunc.getJsonArray("options", message);
                                if (optionArray != null) {
                                    setRealmOptionsList(message);
                                } else {
                                    if (categoryArray != null) {
                                        for (int e = 0; e < categoryArray.length(); e++) {
                                            setRealmOptionsList(generalFunc.getJsonObject(categoryArray, e));
                                        }
                                    }
                                }

                                JSONArray addOnArray = generalFunc.getJsonArray("addon", message);
                                if (addOnArray != null) {
                                    setRealmToppingList(message);
                                } else {
                                    if (categoryArray != null) {
                                        for (int v = 0; v < categoryArray.length(); v++) {
                                            setRealmToppingList(generalFunc.getJsonObject(categoryArray, v));
                                        }
                                    }
                                }
                            }
                        }

                    } else if (generalFunc.getJsonValueStr("iStatusCode", message).equalsIgnoreCase("8")) {
                        deliveryCancelDetails.setVisibility(View.GONE);
                        deliverycanclestatusTxt.setText(generalFunc.getJsonValueStr("OrderStatustext", message));
                        if (!generalFunc.getJsonValueStr("OrderMessage", message).equals("") && generalFunc.getJsonValueStr("CancelOrderMessage", message) != null) {
                            deliveryCancelDetails.setVisibility(View.VISIBLE);
                            deliverycanclestatusTxt.setVisibility(View.GONE);
                            oredrstatusTxt.setVisibility(View.VISIBLE);
                            oredrstatusTxt.setText(generalFunc.getJsonValueStr("CancelOrderMessage", message));
                        }
                    } else if (generalFunc.getJsonValueStr("iStatusCode", message).equalsIgnoreCase("7")) {
                        deliveryCancelDetails.setVisibility(View.VISIBLE);
                        cancelArea.setVisibility(View.GONE);
                        if (!generalFunc.getJsonValueStr("CancelOrderMessage", message).equals("") && generalFunc.getJsonValueStr("CancelOrderMessage", message) != null) {
                            oredrstatusTxt.setVisibility(View.VISIBLE);
                            oredrstatusTxt.setText(generalFunc.getJsonValueStr("CancelOrderMessage", message));
                        }

                    } else {
                        if (findViewById(R.id.btnArea).getVisibility() == View.GONE) {
                            findViewById(R.id.paymentMainArea).setVisibility(View.GONE);
                        } else {
                            findViewById(R.id.PayTypeArea).setVisibility(View.GONE);
                        }
                    }

                    deliverystatusTxt.setText(generalFunc.getJsonValueStr("vStatusNew", message));
                    deliverystatusTxt.setVisibility(View.VISIBLE);
                    addFareDetailLayout(FareDetailsArr);

                    if (findViewById(R.id.PayTypeArea).getVisibility() == View.GONE && helpTxt.getVisibility() == View.VISIBLE) {
                        findViewById(R.id.PayTypeArea).setVisibility(View.INVISIBLE);
                    }


                    if (!generalFunc.getJsonValueStr("CancelOrderReason", message).equals("")) {
                        cancelReasonTxt.setText(generalFunc.getJsonValueStr("CancelOrderReason", message));
                        cancelReasonTxt.setVisibility(View.VISIBLE);
                    }

                    if (eBuyAnyService.equalsIgnoreCase("Yes")) {
                        ((SimpleRatingBar) findViewById(R.id.ratingBar)).setVisibility(View.GONE);
                        btn_type2.setVisibility(View.GONE);


                        if (generalFunc.getJsonValue("is_rating", message) != null && generalFunc.getJsonValueStr("is_rating", message).equalsIgnoreCase("NO")

                                && generalFunc.getJsonValue("iActive", message) != null && generalFunc.getJsonValueStr("iActive", message).equalsIgnoreCase("Yes")) {
                            findViewById(R.id.rateDriverArea).setVisibility(View.VISIBLE);
                            findViewById(R.id.rateCardDriverArea).setVisibility(View.VISIBLE);
                        } else {
                            findViewById(R.id.rateDriverArea).setVisibility(View.GONE);
                            findViewById(R.id.rateCardDriverArea).setVisibility(View.GONE);
                        }
                    }

                    if (vIdProofImageUploaded != null && vIdProofImageUploaded.equalsIgnoreCase("Yes")) {
                        photoArea.setVisibility(View.VISIBLE);


                        new LoadImage.builder(LoadImage.bind(vIdProofImage), iv_proof_img).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

                        iv_proof_img.setOnClickListener(v -> (new ActUtils(getActContext())).openURL(vIdProofImage));

                    }

                    contentView.setVisibility(View.VISIBLE);
                    findViewById(R.id.paymentMainArea).setVisibility(View.VISIBLE);

                } else {
                    generalFunc.showError(true);
                }
            } else {
                generalFunc.showError(true);
            }
        });

    }

    private void setRealmToppingList(JSONObject message) {
        JSONArray addOnArray = generalFunc.getJsonArray("addon", message);
        if (addOnArray != null) {
            for (int i = 0; i < addOnArray.length(); i++) {
                JSONObject topingObject = generalFunc.getJsonObject(addOnArray, i);
                Topping topppingObj = new Topping();
                topppingObj.setfPrice(generalFunc.getJsonValueStr("fPrice", topingObject));
                topppingObj.setfUserPrice(generalFunc.getJsonValueStr("fUserPrice", topingObject));
                topppingObj.setfUserPriceWithSymbol(generalFunc.getJsonValueStr("fUserPriceWithSymbol", topingObject));
                topppingObj.setiFoodMenuId(generalFunc.getJsonValueStr("iFoodMenuId", topingObject));
                topppingObj.setiMenuItemId(generalFunc.getJsonValueStr("iMenuItemId", topingObject));
                topppingObj.setvOptionName(generalFunc.getJsonValueStr("vOptionName", topingObject));
                topppingObj.setiOptionId(generalFunc.getJsonValueStr("iOptionId", topingObject));
                realmToppingList.add(topppingObj);
            }
        }
    }

    private void setRealmOptionsList(JSONObject message) {
        JSONArray optionArray = generalFunc.getJsonArray("options", message);
        if (optionArray != null) {
            for (int i = 0; i < optionArray.length(); i++) {
                JSONObject optionObject = generalFunc.getJsonObject(optionArray, i);
                Options optionsObj = new Options();
                optionsObj.setfPrice(generalFunc.getJsonValueStr("fPrice", optionObject));
                optionsObj.setfUserPrice(generalFunc.getJsonValueStr("fUserPrice", optionObject));
                optionsObj.setfUserPriceWithSymbol(generalFunc.getJsonValueStr("fUserPriceWithSymbol", optionObject));
                optionsObj.setiFoodMenuId(generalFunc.getJsonValueStr("iFoodMenuId", optionObject));
                optionsObj.setiMenuItemId(generalFunc.getJsonValueStr("iMenuItemId", optionObject));
                optionsObj.setvOptionName(generalFunc.getJsonValueStr("vOptionName", optionObject));
                optionsObj.setiOptionId(generalFunc.getJsonValueStr("iOptionId", optionObject));
                optionsObj.seteDefault(generalFunc.getJsonValueStr("eDefault", optionObject));
                realmOptionsList.add(optionsObj);
            }
        }
    }

    private void setImage(String companyImage) {


        if (GenieOrderType != null && GenieOrderType.equalsIgnoreCase("Runner")) {
            runnerImgView.setVisibility(View.VISIBLE);
            restaurantImgView.setVisibility(View.GONE);

        } else {
            if (!Utils.checkText(companyImage)) {
                companyImage = "temp";
            }
            new LoadImage.builder(LoadImage.bind(companyImage), restaurantImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
        }
    }

    private void addItemDetailLayout(JSONArray jobjArray) {
        if (jobjArray != null) {
            for (int i = 0; i < jobjArray.length(); i++) {
                JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
                try {
                    additemDetailRow(jobject.getString("vImage"), jobject.getString("MenuItem"), jobject.getString("SubTitle"), jobject.getString("fTotPrice"), /*" x " + */"" + jobject.get("iQty"), jobject.getString("TotalDiscountPrice"), jobject.has("eExtraPayment") ? jobject.getString("eExtraPayment") : "", jobject.has("eItemAvailable") ? jobject.getString("eItemAvailable") : "", jobject.has("eDecline") ? jobject.getString("eDecline") : "");
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }
    }

    private void addFareDetailLayout(JSONArray jobjArray) {

        if (chargeDetailArea.getChildCount() > 0) {
            chargeDetailArea.removeAllViewsInLayout();
        }

        if (jobjArray != null) {
            for (int i = 0; i < jobjArray.length(); i++) {
                JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
                try {
                    addFareDetailRow(jobject.names().getString(0), jobject.get(jobject.names().getString(0)).toString(), (jobjArray.length() - 1) == i);
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }

    }

    private void addFareDetailRow(String row_name, String row_value, boolean isLast) {
        View convertView = null;
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

            titleVTxt.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            titleHTxt.setText(generalFunc.convertNumberWithRTL(row_name));
            titleVTxt.setText(generalFunc.convertNumberWithRTL(row_value));

            if (isLast) {
                // CALCULATE individual fare & show
                titleHTxt.setTextColor(getResources().getColor(R.color.black));
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);

                titleHTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
                titleVTxt.setTypeface(SystemFont.FontStyle.BOLD.font);
                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
                titleVTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));

            }

        }

        chargeDetailArea.addView(convertView);
    }

    private void additemDetailRow(String itemImage, String menuitemName, String subMenuName, String itemPrice, String qty, String discountprice, String eExtraPayment, String eItemAvailable, String eDecline) {
        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View view = inflater.inflate(R.layout.item_select_bill_design, null);

        MTextView billItems = (MTextView) view.findViewById(R.id.billItems);
        MTextView billItemsQty = (MTextView) view.findViewById(R.id.billItemsQty);
        ImageView imageFoodType = (ImageView) view.findViewById(R.id.imageFoodType);
        CardView foodImageArea = (CardView) view.findViewById(R.id.foodImageArea);

        MTextView serviceTypeNameTxtView = (MTextView) view.findViewById(R.id.serviceTypeNameTxtView);
        MTextView strikeoutbillAmount = (MTextView) view.findViewById(R.id.strikeoutbillAmount);

        final MTextView billAmount = (MTextView) view.findViewById(R.id.billAmount);
        final MTextView billExtraAmount = (MTextView) view.findViewById(R.id.billExtraAmount);
        final MTextView itemNoteTxt = (MTextView) view.findViewById(R.id.itemNoteTxt);
        itemNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ITEM_NOT_AVAILABLE"));


        if (itemImage.equalsIgnoreCase("")) {
            imageFoodType.setVisibility(View.GONE);
            foodImageArea.setVisibility(View.GONE);
        }


        new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(getActContext(), itemImage, size, size)), imageFoodType).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

        if (Utils.checkText(eDecline) && eDecline.equalsIgnoreCase("Yes")) {
            billAmount.setTextColor(getResources().getColor(R.color.red));
            billAmount.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINED"));
        } else {
            billAmount.setTextColor(Color.parseColor("#272727"));
            billAmount.setText(generalFunc.convertNumberWithRTL(itemPrice));
        }
        billItemsQty.setText(generalFunc.convertNumberWithRTL(qty));

        if (!eDecline.equalsIgnoreCase("Yes") && itemPrice.contains("--")) {
            billAmount.setVisibility(View.GONE);
        }

        billItems.setText(menuitemName);


        if (!subMenuName.equalsIgnoreCase("")) {
            serviceTypeNameTxtView.setVisibility(View.VISIBLE);
            serviceTypeNameTxtView.setText(subMenuName);
        } else {
            serviceTypeNameTxtView.setVisibility(View.GONE);
        }

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
            if (eDecline.equalsIgnoreCase("Yes")) {
                billExtraAmount.setVisibility(View.GONE);
                billAmount.setVisibility(View.VISIBLE);
            } else {
                billExtraAmount.setVisibility(View.VISIBLE);
                billAmount.setVisibility(View.GONE);
            }
        } else {

            if (discountprice != null && !discountprice.equals("")) {
                SpannableStringBuilder spanBuilder = new SpannableStringBuilder();

                SpannableString origSpan = new SpannableString(billAmount.getText());

                origSpan.setSpan(new StrikethroughSpan(), 0, billAmount.getText().length(), Spanned.SPAN_INCLUSIVE_EXCLUSIVE);

                spanBuilder.append(origSpan);

                strikeoutbillAmount.setVisibility(View.VISIBLE);
                strikeoutbillAmount.setText(spanBuilder);
                billAmount.setText(discountprice);
                if (!eDecline.equalsIgnoreCase("Yes")) {
                    billAmount.setTextColor(getResources().getColor(R.color.appThemeColor_1));
                }

                if (discountprice.contains("--")) {
                    billAmount.setVisibility(View.GONE);
                    strikeoutbillAmount.setVisibility(View.GONE);
                }
            } else {
                strikeoutbillAmount.setVisibility(View.GONE);
                if (!eDecline.equalsIgnoreCase("Yes")) {
                    billAmount.setTextColor(getResources().getColor(R.color.appThemeColor_1));
                }
                billAmount.setPaintFlags(billAmount.getPaintFlags());
            }
        }


        billDetails.addView(view);
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.tipAmountArea1) {
            setSelected(tipAmountText1, tipAmountArea1);
        }
        if (i == R.id.tipAmountArea2) {
            setSelected(tipAmountText2, tipAmountArea2);
        }
        if (i == R.id.tipAmountArea3) {
            setSelected(tipAmountText3, tipAmountArea3);
        }
        if (i == R.id.closeImg1) {
            removeSelectedTip(true);
        }
        if (i == R.id.closeImg2) {
            removeSelectedTip(true);
        }
        if (i == R.id.closeImg3) {
            removeSelectedTip(true);
        }
        if (i == R.id.closeImgOther) {
            removeSelectedTip(true);
        }
        if (i == R.id.tipAmountAreaOther) {
            setSelected(tipAmountOtherText, tipAmountAreaOther);
        }
        if (i == R.id.tipDescHintTitleText) {
            showTipInfoDialog(getActContext().getResources().getDrawable(R.drawable.ic_save_money), "LBL_DELIVERY_TIP_DESC");
        }
        if (i == R.id.iv_tip_help) {
            showTipInfoDialog(getActContext().getResources().getDrawable(R.drawable.ic_save_money), "LBL_DELIVERY_TIP_DESC");
        }
        if (i == giveTipArea.getId()) {
//               giveTip();

//                tipAmount = "";
            if (addTipArea.getVisibility() == View.VISIBLE) {
                final boolean tipAmountEntered = Utils.checkText(tipAmountBox);
                if (tipAmountEntered == false) {
                    amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                    errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REQUIRED"));
                    errorTxt.setVisibility(View.VISIBLE);
                    return;
                } else {
                    resetErrorTxt();

                }

                if (GeneralFunctions.parseDoubleValue(0, tipAmountBox.getText().toString()) > 0) {
                    resetErrorTxt();
                    setSelected(tipAmountOtherText, tipAmountAreaOther);
                    giveTip();

                } else {
                    amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                    errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_GRATER_ZERO_VALUE_TXT"));
                    errorTxt.setVisibility(View.VISIBLE);

                }
            } else {
                giveTip();
            }

        }
        if (i == R.id.tipGivenBtn) {
//                tipAmount = "";

            final boolean tipAmountEntered = Utils.checkText(tipAmountBox);
            if (tipAmountEntered == false) {
                amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REQUIRED"));
                errorTxt.setVisibility(View.VISIBLE);
                return;
            } else {
                resetErrorTxt();

            }

            if (GeneralFunctions.parseDoubleValue(0, tipAmountBox.getText().toString()) > 0) {
                resetErrorTxt();
                setSelected(tipAmountOtherText, tipAmountAreaOther);

            } else {
                amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrecterrorwithdesign));
                errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_GRATER_ZERO_VALUE_TXT"));
                errorTxt.setVisibility(View.VISIBLE);

            }

        }
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.viewPreferenceArea) {
            Bundle bundle = new Bundle();
            bundle.putString("DeliveryPreferences", DeliveryPreferences.toString());
            new ActUtils(getActContext()).startActWithData(UserPrefrenceActivity.class, bundle);
        } else if (i == helpTxt.getId()) {
            Bundle bn = new Bundle();
            bn.putString("iOrderId", iOrderId);
            new ActUtils(getActContext()).startActWithData(HelpMainCategory23Pro.class, bn);
        } else if (i == btn_type2.getId()) {
            manageReorder();
        } else if (view.getId() == R.id.viewPrescriptionArea || view.getId() == R.id.viewPrescTxtView) {
            Bundle bn = new Bundle();
            bn.putSerializable("imageList", imageList);
            bn.putBoolean("isOrder", true);
            (new ActUtils(getActContext())).startActWithData(PrescriptionActivity.class, bn);

        } else if (view.getId() == R.id.receiptImgView) {
            sendReceipt();
        } else if (i == rateBtnId) {
            if ((ufxratingBar).getRating() <= 0.0) {
                generalFunc.showMessage(generalFunc.getCurrentView(OrderDetailsActivity.this), generalFunc.retrieveLangLBl("", "LBL_ERROR_RATING_DIALOG_TXT"));
                return;
            }
            submitRating();
        }

    }

    @Override
    public void onBackPressed() {

        if (isRestart) {
            Bundle bn = new Bundle();
            new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
            finishAffinity();
        } else {

            super.onBackPressed();
        }
    }

    private void showTipInfoDialog(Drawable drawable, String LBL_DATA) {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.design_tip_help, null);
        builder.setView(dialogView);


        final ImageView iamage_source = (ImageView) dialogView.findViewById(R.id.iamage_source);
        if (drawable != null) {
            iamage_source.setImageDrawable(drawable);
        }
        final MTextView okTxt = (MTextView) dialogView.findViewById(R.id.okTxt);
        final MTextView titileTxt = (MTextView) dialogView.findViewById(R.id.titileTxt);
        final MTextView msgTxt = (MTextView) dialogView.findViewById(R.id.msgTxt);
        titileTxt.setText(generalFunc.retrieveLangLBl("Tip your delivery partner", "LBL_TIP_DIALOG_TITLE"));
        okTxt.setText(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
        msgTxt.setText("" + generalFunc.retrieveLangLBl("You can set a delivery preference before the delivery agent attempts to deliver your package at your door. Your preferences will be taken care by delivery driver.", LBL_DATA));
        msgTxt.setMovementMethod(new ScrollingMovementMethod());
        okTxt.setOnClickListener(v -> giveTipAlertDialog.dismiss());
        giveTipAlertDialog = builder.create();
        giveTipAlertDialog.setCancelable(true);
        LayoutDirection.setLayoutDirection(giveTipAlertDialog);
        giveTipAlertDialog.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        giveTipAlertDialog.show();


    }

    private void removeSelectedTip(boolean isCallFareEstimate) {
        tipAmount = "";
        tipAmountBox.setText("");
        tipAmountArea1.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        tipAmountArea2.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        tipAmountArea3.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        tipAmountAreaOther.setBackground(getActContext().getResources().getDrawable(R.drawable.button_normal_background));
        closeImg1.setVisibility(View.GONE);
        closeImg2.setVisibility(View.GONE);
        closeImg3.setVisibility(View.GONE);
        closeImgOther.setVisibility(View.GONE);
        if (!isPercentage) {
            tipAmountOtherText.setTag("");
        }
        tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        addTipArea.setVisibility(View.GONE);
        resetErrorTxt();
    }

    private void setSelected(MTextView selectedTextAreaTxtView, LinearLayout selectedArea) {
        tipAmount = selectedTextAreaTxtView.getTag().toString();
        addTipArea.setVisibility(selectedArea == tipAmountAreaOther ? View.VISIBLE : View.GONE);
        resetErrorTxt();
        errorTxt.setVisibility(selectedArea == tipAmountAreaOther ? View.VISIBLE : View.GONE);
        tipAmountArea1.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea1 ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImg1.setVisibility(selectedArea == tipAmountArea1 ? View.VISIBLE : View.GONE);
        tipAmountArea2.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea2 ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImg2.setVisibility(selectedArea == tipAmountArea2 ? View.VISIBLE : View.GONE);
        tipAmountArea3.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountArea3 ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImg3.setVisibility(selectedArea == tipAmountArea3 ? View.VISIBLE : View.GONE);
        tipAmountAreaOther.setBackground(getActContext().getResources().getDrawable(selectedArea == tipAmountAreaOther ? R.drawable.button_background : R.drawable.button_normal_background));
        closeImgOther.setVisibility(selectedArea == tipAmountAreaOther && Utils.checkText(Utils.getText(tipAmountBox)) ? View.VISIBLE : View.GONE);


        if (selectedArea == tipAmountAreaOther && Utils.checkText(Utils.getText(tipAmountBox))) {
            if (!isPercentage) {
                tipAmountOtherText.setTag(Utils.getText(tipAmountBox));
                tipAmount = Utils.getText(tipAmountBox);

            }
            tipAmountOtherText.setText(currencySymbol + Utils.getText(tipAmountBox));
            closeImgOther.setVisibility(View.VISIBLE);
            addTipArea.setVisibility(View.GONE);
            resetErrorTxt();
        } else if (selectedArea != tipAmountAreaOther) {
            if (!isPercentage) {
                tipAmountOtherText.setTag("");
            }

            tipAmountBox.setText("");
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        } else if ((selectedArea == tipAmountAreaOther && !Utils.checkText(Utils.getText(tipAmountBox))) && Utils.checkText(tipAmount)) {
            if (!isPercentage) {
                tipAmountOtherText.setTag("");
            }
            tipAmountBox.setText("");
            tipAmount = "";
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        } else {
            tipAmountBox.setText("");
            if (!isPercentage) {
                tipAmountOtherText.setTag("");
            }
            tipAmountOtherText.setText(generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
        }

        if (selectedTextAreaTxtView == tipAmountText1) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText1.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountText2) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText2.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountText3) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountText3.setTextColor(getResources().getColor(R.color.black));
        }
        if (selectedTextAreaTxtView == tipAmountOtherText) {
            selectedTextAreaTxtView.setTextColor(getResources().getColor(R.color.appThemeColor_TXT_1));
        } else {
            tipAmountOtherText.setTextColor(getResources().getColor(R.color.black));
        }
    }

    private void resetErrorTxt() {
        errorTxt.setText("");
        errorTxt.setVisibility(View.GONE);
        amountArea.setBackground(getActContext().getResources().getDrawable(R.drawable.roundrectwithdesign));
    }

    private void giveTip() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "OrderCollectTip");
        parameters.put("iOrderId", iOrderId);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.userType);
        parameters.put("eSystem", Utils.eSystem_Type);
        if (Utils.checkText(tipAmount)) {
            if (isPercentage) {
                parameters.put("selectedTipPos", tipAmount);
                if (tipAmount.equalsIgnoreCase("4")) {
                    parameters.put("fTipAmount", Utils.getText(tipAmountBox));
                }
            } else {
                parameters.put("fTipAmount", Utils.checkText(tipAmountBox) ? Utils.getText(tipAmountBox) : tipAmount);
            }
        } else {
            parameters.put("fTipAmount", Utils.checkText(tipAmountBox) ? Utils.getText(tipAmountBox) : tipAmount);
        }

        parameters.put("iMemberId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                String action = generalFunc.getJsonValue(Utils.action_str, responseString);
                if (action.equals("1")) {
                    String paymentUrl = generalFunc.getJsonValue("paymentUrl", responseString);
                    if (Utils.checkText(paymentUrl)) {
                        Bundle bn = new Bundle();
                        HashMap<String, Object> map = new HashMap<>();
                        map.put("URL", paymentUrl);
                        map.put("vPageTitle", generalFunc.retrieveLangLBl("", "LBL_GIVE_TIP"));
                        bn.putSerializable("data", map);
                        new ActUtils(getActContext()).startActForResult(QuickPaymentActivity.class, bn, Utils.REQ_VERIFY_INSTANT_PAYMENT_CODE);
                        return;
                    }
                    showTipSuccessMsg();
                } else {

                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    if (message.equalsIgnoreCase("LBL_NO_CARD_AVAIL_NOTE")) {

                        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_ADD_CARD"));
                        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                        generateAlert.setBtnClickList(btn_id -> {
                            if (btn_id == 1) {
                                generateAlert.closeAlertBox();
                                Bundle bn = new Bundle();
                                new ActUtils(getActContext()).startActForResult(CardPaymentActivity.class, bn, Utils.CARD_PAYMENT_REQ_CODE);
                            } else {
                                generateAlert.closeAlertBox();
                            }
                        });
                        generateAlert.showAlertBox();
                    } else if (message.equalsIgnoreCase("LBL_REQUIRED_MINIMUM_AMOUT")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }

                }
            } else {
                generalFunc.showError();

            }
        });


    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.REQ_VERIFY_INSTANT_PAYMENT_CODE && resultCode == RESULT_OK && data != null) {
//            if (data != null) {
            showTipSuccessMsg();
//            } else {
//                generalFunc.showError();
//            }
        }
    }

    private void showTipSuccessMsg() {
        dialog_sucess = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        dialog_sucess.setContentView(R.layout.sucess_layout_nw);
        MTextView titleTxt = (MTextView) dialog_sucess.findViewById(R.id.titleTxt);
        MTextView msgTxt = (MTextView) dialog_sucess.findViewById(R.id.msgTxt);
        msgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_TIP_SUCCESS_TXT"));

        FrameLayout dialogImgAlpha = (FrameLayout) dialog_sucess.findViewById(R.id.dialogImgAlpha);
        dialogImgAlpha.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_circle));
        dialogImgAlpha.getBackground().setAlpha(70);

        MButton btn_type2 = ((MaterialRippleLayout) dialog_sucess.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));


        btn_type2.setOnClickListener(view -> {
            dialog_sucess.dismiss();
            tipCardArea.setVisibility(View.GONE);
            getOrderDetails();
        });

        dialog_sucess.setCancelable(false);
        dialog_sucess.show();

    }

    public void sendReceipt() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getReceiptOrder");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iOrderId", iOrderId);
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
            } else {
                generalFunc.showError();
            }
        });

    }

    public void submitRating() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "submitRating");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iGeneralUserId", generalFunc.getMemberId());
        parameters.put("iOrderId", iOrderId);
        parameters.put("rating", "" + ufxratingBar.getRating());
        parameters.put("message", Utils.getText(commentBox));
        parameters.put("UserType", Utils.app_type);
        parameters.put("eFromUserType", Utils.userType);
        parameters.put("eToUserType", "Driver");
        parameters.put("eSystem", Utils.eSystem_Type);


        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(true);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();

                        Intent returnIntent = new Intent();
                        setResult(Activity.RESULT_OK, returnIntent);
                        finish();
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_TRIP_RATING_FINISHED_TXT"));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                    generateAlert.setCancelable(false);


                } else {
                    resetRatingData();
                    generalFunc.showMessage(generalFunc.getCurrentView(OrderDetailsActivity.this), generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void resetRatingData() {
        commentBox.setText("");
        ((RatingBar) findViewById(ratingBar)).setRating(0);
    }

    public void addDataToList(Realm realm) {
        ArrayList<String> removeData = new ArrayList<>();
        removeData.add(Utils.COMPANY_ID);
        removeData.add(Utils.COMPANY_MINIMUM_ORDER);
        generalFunc.removeValue(removeData);
        generalFunc.removeAllRealmData(realm);

        if ((realmOptionsList != null && realmOptionsList.size() > 0) || (realmToppingList != null && realmToppingList.size() > 0)) {
            storeAllOptionsToRealm();
        }

        setRealmdData();
    }

    public void storeAllOptionsToRealm() {
        Realm realm = MyApp.getRealmInstance();
        realm.beginTransaction();
        realm.insertOrUpdate(realmToppingList);
        realm.insertOrUpdate(realmOptionsList);
        realm.commitTransaction();
    }

    public void setRealmdData() {

        Realm realm = MyApp.getRealmInstance();

        for (int i = 0; i < cartList.size(); i++) {

            HashMap<String, String> cartData = cartList.get(i);
            realm.beginTransaction();
            //if (cart == null) {
            isCartNull = true;
            Cart cart = new Cart();
            cart.setvItemType(cartData.get("vItemType"));
            cart.setvImage(cartData.get("vImage"));
            cart.setfDiscountPrice(cartData.get("fDiscountPrice"));
            cart.setiMenuItemId(cartData.get("iMenuItemId"));
            cart.seteFoodType(cartData.get("eFoodType"));
            cart.setiServiceId(iServiceId);
            cart.setiFoodMenuId(cartData.get("iFoodMenuId"));
            cart.setiCompanyId(cartData.get("iCompanyId"));
            cart.setvCompany(cartData.get("vCompany"));
            cart.setCurrencySymbol(currencySymbol);
            cart.setQty(cartData.get("Qty"));
            cart.setIsOption(cartData.get("isOption"));
            cart.setIsTooping(cartData.get("isTopping"));
            cart.setIspriceshow(cartData.get("ispriceshow"));
            cart.setMilliseconds(Calendar.getInstance().getTimeInMillis());
            if (Utils.checkText(cartData.get("categoryArray"))) {
                cart.setMultiItemJsonData(cartData.get("categoryArray"));
            }
            cart.setiOptionId(cartData.get("optionId"));
            cart.setiToppingId(cartData.get("iToppingId"));
//            if (isCartNull) {
            realm.insert(cart);
//            } else {
//                realm.insertOrUpdate(cart);
//            }

            realm.commitTransaction();
        }

        generalFunc.storeData(Utils.COMPANY_MINIMUM_ORDER, fMinOrderValue);
        generalFunc.storeData(Utils.COMPANY_ID, iCompanyId);

        Bundle bn = new Bundle();
        bn.putBoolean("isRestart", true);
        new ActUtils(getActContext()).startActWithData(EditCartActivity.class, bn);
    }


    public void manageReorder() {
        Realm realm = MyApp.getRealmInstance();
        RealmResults<Cart> cartRealmList = realm.where(Cart.class).findAll();

        if (cartRealmList != null && !cartRealmList.isEmpty()) {

            GenerateAlertBox generateAlertBox = new GenerateAlertBox(getActContext());
            generateAlertBox.setCancelable(false);
            generateAlertBox.setContentMessage(generalFunc.retrieveLangLBl("", "LBL_UPDATE_CART"), generalFunc.retrieveLangLBl("Are you sure you'd like to change restaurants ? Your order will be lost.", "LBL_ORDER_LOST_ALERT_TXT"));
            generateAlertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
            generateAlertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_PROCEED"));
            generateAlertBox.setBtnClickList(btn_id -> {
                if (btn_id == 1) {
                    deleteOptionToRealm();

                    addDataToList(realm);

                } else {
                    generateAlertBox.closeAlertBox();
                }
            });
            generateAlertBox.showAlertBox();

        } else {
            deleteOptionToRealm();
            addDataToList(realm);
        }
    }


    public void deleteOptionToRealm() {
        Realm realm = MyApp.getRealmInstance();
        realm.beginTransaction();
        realm.delete(Options.class);
        realm.commitTransaction();

        Realm realmTopping = MyApp.getRealmInstance();
        realmTopping.beginTransaction();
        realmTopping.delete(Topping.class);
        realmTopping.commitTransaction();
    }

    public Context getActContext() {
        return OrderDetailsActivity.this;
    }
}
