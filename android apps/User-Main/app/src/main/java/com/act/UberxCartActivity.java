package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.Typeface;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.InputType;
import android.text.SpannableStringBuilder;
import android.text.method.LinkMovementMethod;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ScrollView;
import android.widget.TextView;

import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.adapter.files.CategoryListItem;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.MyClickableSpan;
import com.buddyverse.main.R;
import com.realmModel.CarWashCartData;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.WKWebView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.math.BigDecimal;
import java.util.HashMap;

import io.realm.Realm;
import io.realm.RealmResults;

public class UberxCartActivity extends ParentActivity {

    MTextView titleTxt, headingTxt, commentHname;
    ImageView backImgView, minusImgView, addImgView;

    CategoryListItem categoryListItem;
    MaterialEditText commentBox;
    LinearLayout fareDetailDisplayArea, qtyArea;
    MButton btn_type2;
    int submitBtnId, qty = 1;
    MTextView QTYNumberTxtView, removeCartTxt, descMoreLessTxt;
    //MTextView descTxt;
    WKWebView descWebView;
    ScrollView scrollArea;
    String iMaxQty = "", eAllowQty = "", eFareType = "", vSymbol = "", eVideoConsultServiceCharge = "", finalTotal = "", finalTotalNw = "", vCategoryDescUrl = "";
    RealmResults<CarWashCartData> realmCartList;

    Boolean isDescExpand = false;
    CarWashCartData cart = null;
    private boolean isVideoConsultEnable;
    private JSONArray FareDetailsArrNewObj = null;
    private Handler handler;

    @SuppressLint("SetTextI18n")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_uberx_cart);


        categoryListItem = (CategoryListItem) getIntent().getSerializableExtra("data");
        realmCartList = getCartData();
        Realm realm = MyApp.getRealmInstance();
        cart = checkSameRecordExist(realm);

        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        removeCartTxt = (MTextView) findViewById(R.id.removeCartTxt);
        removeCartTxt.setText(generalFunc.retrieveLangLBl("Remove From Cart", "LBL_UFX_REMOVE_FROM_CART"));
        addToClickHandler(removeCartTxt);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_SERVICE_TXT"));
        commentHname = (MTextView) findViewById(R.id.commentHname);
        commentBox = (MaterialEditText) findViewById(R.id.commentBox);
        if (cart != null) {
            qty = GeneralFunctions.parseIntegerValue(0, cart.getItemCount());
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_UFX_EDIT_CART"));
            commentBox.setText(cart.getSpecialInstruction());
            removeCartTxt.setVisibility(View.VISIBLE);
        }
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        headingTxt = (MTextView) findViewById(R.id.headingTxt);

        isVideoConsultEnable = getIntent().getBooleanExtra("isVideoConsultEnable", false);
        if (isVideoConsultEnable) {
            titleTxt.setText(generalFunc.retrieveLangLBl("Cart", "LBL_SPECIAL_INSTRUCTION_TXT"));
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_NEXT"));
            removeCartTxt.setVisibility(View.GONE);
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("Cart", "LBL_UFX_CART"));
        }

        backImgView = (ImageView) findViewById(R.id.backImgView);
        //descTxt = (MTextView) findViewById(R.id.descTxt);
        descMoreLessTxt = findViewById(R.id.descMoreLessTxt);
        descWebView = (WKWebView) findViewById(R.id.descWebView);
        scrollArea = (ScrollView) findViewById(R.id.scrollArea);
        minusImgView = (ImageView) findViewById(R.id.minusImgView);
        addImgView = (ImageView) findViewById(R.id.addImgView);
        QTYNumberTxtView = (MTextView) findViewById(R.id.QTYNumberTxtView);
        QTYNumberTxtView.setText(qty + "");
        qtyArea = (LinearLayout) findViewById(R.id.qtyArea);
        addToClickHandler(minusImgView);
        addToClickHandler(addImgView);
        fareDetailDisplayArea = (LinearLayout) findViewById(R.id.fareDetailDisplayArea);
        commentBox.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        commentBox.setSingleLine(false);
        commentBox.setHideUnderline(true);
        commentBox.setGravity(Gravity.START | Gravity.TOP);
        commentBox.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
//        commentBox.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.login_fb_border));
        commentBox.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.material_card_gray));
        if (generalFunc.isRTLmode()) {
            commentBox.setPaddings((int) getResources().getDimension(R.dimen._5sdp), 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
            backImgView.setRotation(180);
        } else {
            commentBox.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, (int) getResources().getDimension(R.dimen._5sdp), 0);
        }
        commentBox.setIncludeFontPadding(false);

        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);
        addToClickHandler(backImgView);
        commentHname.setText(generalFunc.retrieveLangLBl("", "LBL_INS_PROVIDER_BELOW"));

        headingTxt.setText(categoryListItem.getvTitle());
        descMoreLessTxt.setText("+" + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"));
        setwebvieSetting();
        addToClickHandler(descMoreLessTxt);

        descWebView.setVisibility(View.GONE);
        descMoreLessTxt.setVisibility(View.GONE);

        ViewGroup.LayoutParams vc = descWebView.getLayoutParams();
        vc.height = 150;
        descWebView.setLayoutParams(vc);

        //descWebView.setOnTouchListener((v, event) -> false);
        descWebView.setOnTouchListener(new WebViewTouchListener());


        getDetails();

    }


    @SuppressLint("SetJavaScriptEnabled")
    private void setwebvieSetting() {
        final WebSettings webSettings = descWebView.getSettings();
        //For disable click
        descWebView.setOnLongClickListener(v -> true);
        descWebView.setLongClickable(true);
        //For fit all contnent in device screen
        webSettings.setLoadWithOverviewMode(true);
        webSettings.setUseWideViewPort(true);


    }

    public void makeTextViewResizable(final TextView tv, final int maxLine, final String expandText, final boolean viewMore, final int color, final int dimension) {
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
                    tv.setText(a(tv.getText().toString(), tv, maxLine, expandText, viewMore, color, dimension, (GeneralFunctions.ResizableTexViewClickListener) null), TextView.BufferType.SPANNABLE);
                } else if (maxLine > 0 && tv.getLineCount() >= maxLine) {
                    var2 = tv.getLayout().getLineEnd(maxLine - 1);
                    var3 = tv.getText().subSequence(0, var2 - expandText.length() + 1) + " " + expandText;
                    tv.setText(var3);
                    tv.setMovementMethod(LinkMovementMethod.getInstance());
                    tv.setText(a(tv.getText().toString(), tv, maxLine, expandText, viewMore, color, dimension, (GeneralFunctions.ResizableTexViewClickListener) null), TextView.BufferType.SPANNABLE);
                } else if (tv.getLineCount() > maxLine) {
                    var2 = tv.getLayout().getLineEnd(tv.getLayout().getLineCount() - 1);
                    var3 = tv.getText().subSequence(0, var2) + " " + expandText;
                    tv.setText(var3);
                    tv.setMovementMethod(LinkMovementMethod.getInstance());
                    tv.setText(a(tv.getText().toString(), tv, var2, expandText, viewMore, color, dimension, (GeneralFunctions.ResizableTexViewClickListener) null), TextView.BufferType.SPANNABLE);
                }

            }
        });
    }

    private SpannableStringBuilder a(String var1, final TextView var2, int var3, String var4, final boolean var5, int var6, int var7, final GeneralFunctions.ResizableTexViewClickListener var8) {
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
                                bn.putString("staticData", categoryListItem.getvDesc());
                                bn.putString("vTitle", categoryListItem.getvTitle());
                                new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
                            } else {
                                var2.setLayoutParams(var2.getLayoutParams());
                                var2.setText(var2x.get("TitleOfTextView").toString(), TextView.BufferType.SPANNABLE);
                                var2.invalidate();
                                makeTextViewResizable(var2, GeneralFunctions.parseIntegerValue(2, var2x.get("OrigMaxLines").toString()), "...\n+ " + generalFunc.retrieveLangLBl("View More", "LBL_VIEW_MORE_TXT"), true, var6, var7);
                            }
                        }

                    }
                }
            }, var1.indexOf(var4), var1.indexOf(var4) + var4.length(), 0);
        }

        return var9;
    }

    public RealmResults<CarWashCartData> getCartData() {
        try {
            Realm realm = MyApp.getRealmInstance();
            return realm.where(CarWashCartData.class).findAll();
        } catch (Exception e) {
            Logger.d("RealmException", "::" + e.toString());

        }
        return null;
    }

    private void addFareDetailLayout(JSONArray jobjArray, int qty) {

        if (FareDetailsArrNewObj == null) {
            return;
        }

        if (fareDetailDisplayArea.getChildCount() > 0) {
            fareDetailDisplayArea.removeAllViewsInLayout();
        }

        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String data = jobject.names().getString(0);
                if (jobject.length() == 1) {
                    addFareDetailRow(data, jobject.get(data).toString(), (jobjArray.length() - 1) == i, qty, false);
                } else if (jobject.length() == 2) {
                    String eReverseformattingEnable = jobject.names().getString(1);
                    if (eReverseformattingEnable.equalsIgnoreCase("eReverseformattingEnable")) {
                        addFareDetailRow(data, jobject.get(data).toString(), (jobjArray.length() - 1) == i, qty, jobject.get(eReverseformattingEnable).toString().equalsIgnoreCase("Yes"));
                    } else {
                        addFareDetailRow(data, jobject.get(data).toString(), (jobjArray.length() - 1) == i, qty, false);
                    }
                }
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

    }

    @SuppressLint("InflateParams")
    private void addFareDetailRow(String row_name, String row_value, boolean isLast, int qty, boolean eReverseformattingEnable) {
        View convertView;
        Typeface font = SystemFont.FontStyle.SEMI_BOLD.font;
        if (row_name.equalsIgnoreCase("eDisplaySeperator")) {
            convertView = new View(getActContext());
            convertView.setBackgroundColor(Color.parseColor("#dedede"));
        } else {
            LayoutInflater infalInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = infalInflater.inflate(R.layout.design_fare_deatil_row, null);

            MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
            MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);

            String valuAfter = row_value.replace(vSymbol, "");
            double qtyWiseVal;

            try {
                qtyWiseVal = GeneralFunctions.parseDoubleValue(0, valuAfter) * qty;
            } catch (Exception e) {

                valuAfter = valuAfter.replace(",", "");
                qtyWiseVal = GeneralFunctions.parseDoubleValue(0, valuAfter) * qty;

            }


            titleHTxt.setText(generalFunc.convertNumberWithRTL(row_name));
            if (eReverseformattingEnable) {
                titleVTxt.setText(row_value);
            } else {
                titleVTxt.setText(generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(qtyWiseVal))), vSymbol, true));
            }

            titleHTxt.setTextColor(Color.parseColor("#656565"));
            titleVTxt.setTextColor(Color.parseColor("#1c1c1c"));
            if (isLast && Utils.checkText(eFareType) && !eFareType.equalsIgnoreCase("Regular")) {

                titleVTxt.setTextSize(TypedValue.COMPLEX_UNIT_PX, getActContext().getResources().getDimension(R.dimen._15sdp));
                titleHTxt.setTextSize(TypedValue.COMPLEX_UNIT_PX, getActContext().getResources().getDimension(R.dimen._15sdp));

                titleHTxt.setTextColor(Color.parseColor("#000000"));
                titleHTxt.setTypeface(font);
                titleVTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));
                titleVTxt.setTypeface(font);

            }


            Logger.d("FinalTotalDataRowVal", "::11::" + qtyWiseVal);
            String qtyWiseValNw = "" + BigDecimal.valueOf(qtyWiseVal).setScale(2,
                    BigDecimal.ROUND_HALF_UP);

            Logger.d("FinalTotalDataRowVal", "::22::" + qtyWiseValNw);
            if (isLast) {
                finalTotal = vSymbol + qtyWiseValNw;
                finalTotalNw = generalFunc.formatNumAsPerCurrency(generalFunc, "" + qtyWiseValNw, vSymbol, true);
            }
        }
        fareDetailDisplayArea.addView(convertView);
        findViewById(R.id.fareArea).setVisibility(View.VISIBLE);
    }

    public void getDetails() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getVehicleTypeDetails");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("SelectedCabType", Utils.CabGeneralType_UberX);
        parameters.put("iVehicleTypeId", categoryListItem.getiVehicleTypeId());
        parameters.put("iDriverId", getIntent().getStringExtra("iDriverId"));

        parameters.put("eForVideoConsultation", isVideoConsultEnable ? "Yes" : "No");

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail) {

                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    iMaxQty = generalFunc.getJsonValue("iMaxQty", message);
                    eAllowQty = generalFunc.getJsonValue("eAllowQty", message);
                    eFareType = generalFunc.getJsonValue("eFareType", message);
                    vSymbol = generalFunc.getJsonValue("vSymbol", message);
                    vCategoryDescUrl = generalFunc.getJsonValue("vCategoryDesc", message);
                    eVideoConsultServiceCharge = generalFunc.getJsonValue("eVideoConsultServiceCharge", message);
                    if (eAllowQty.equalsIgnoreCase("Yes")) {
                        qtyArea.setVisibility(View.VISIBLE);
                    }

                    if (!vCategoryDescUrl.equals("")) {
                        descWebView.loadUrl(vCategoryDescUrl);
                    } else {
                        descWebView.setVisibility(View.GONE);
                        descMoreLessTxt.setVisibility(View.GONE);
                    }

                    descWebView.setWebViewClient(new WebViewClient() {
                        public void onPageFinished(WebView view, String url) {
                            descWebView.setVisibility(View.VISIBLE);
                            handler = new Handler(Looper.getMainLooper());
                            handler.postDelayed(() -> {
                                if (view.getContentHeight() > 130) {
                                    descMoreLessTxt.setVisibility(View.VISIBLE);
                                }
                            }, 500);

                        }
                    });

                    FareDetailsArrNewObj = generalFunc.getJsonArray("fareDetails", message);


                    addFareDetailLayout(FareDetailsArrNewObj, qty);


                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message", responseString)));
                }

            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }

    public Context getActContext() {
        return UberxCartActivity.this;
    }

    public void setRealmData() {
        Realm realm = MyApp.getRealmInstance();
        CarWashCartData cart = checkSameRecordExist(realm);

        realm.beginTransaction();

        boolean isCartNull = false;
        if (cart == null) {
            isCartNull = true;
            cart = new CarWashCartData();
            cart.setCategoryListItem(categoryListItem);
            cart.setDriverId(getIntent().getStringExtra("iDriverId"));
        }
        cart.setSpecialInstruction(Utils.getText(commentBox));
        cart.setItemCount(QTYNumberTxtView.getText().toString().trim());
        cart.setFinalTotal(finalTotal);
        cart.setVideoConsultEnable(isVideoConsultEnable);

        cart.setFinalTotalNw(finalTotalNw);
        cart.setvSymbol(vSymbol);
        if (isCartNull) {
            realm.insert(cart);
        } else {
            realm.insertOrUpdate(cart);
        }

        realm.commitTransaction();
        Intent returnIntent = new Intent();
        returnIntent.putExtra("isVideoConsultEnable", isVideoConsultEnable);
        setResult(Activity.RESULT_OK, returnIntent);
        finish();
    }

    public CarWashCartData checkSameRecordExist(Realm realm) {
        RealmResults<CarWashCartData> cartlist = realm.where(CarWashCartData.class).findAll();
        if (cartlist != null && cartlist.size() > 0)
            for (int i = 0; i < realmCartList.size(); i++) {
                if (categoryListItem.getiVehicleTypeId().equalsIgnoreCase(realmCartList.get(i).getCategoryListItem().getiVehicleTypeId())) {
                    return realmCartList.get(i);
                }
            }
        return null;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(UberxCartActivity.this);
        if (view == backImgView) {
            onBackPressed();
        } else if (view == btn_type2) {
            setRealmData();

        } /*else if (view == descTxt) {
            Bundle bn = new Bundle();
            bn.putString("staticData", categoryListItem.getvDesc());
            bn.putString("vTitle", categoryListItem.getvTitle());
            new ActUtils(getActContext()).startActWithData(StaticPageActivity.class, bn);
        }*/ else if (view == addImgView) {


            int QUANTITY = Integer.parseInt(QTYNumberTxtView.getText().toString());


            if (QUANTITY < GeneralFunctions.parseIntegerValue(0, iMaxQty)) {

                if (QUANTITY >= 1) {
                    QUANTITY = QUANTITY + 1;

                    QTYNumberTxtView.setText(generalFunc.convertNumberWithRTL("" + QUANTITY));
                    addFareDetailLayout(FareDetailsArrNewObj, QUANTITY);
                }
            } else {
                generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("", "LBL_QUANTITY_CLOSED_TXT"));
            }

        } else if (view == minusImgView) {

            int QUANTITY = Integer.parseInt(QTYNumberTxtView.getText().toString());
            if (QUANTITY > 1) {
                QUANTITY = QUANTITY - 1;
                QTYNumberTxtView.setText(generalFunc.convertNumberWithRTL("" + QUANTITY));

                addFareDetailLayout(FareDetailsArrNewObj, QUANTITY);
            }

        } else if (view == removeCartTxt) {
            Realm realm = MyApp.getRealmInstance();
            realm.beginTransaction();
            if (cart != null) {
                cart.deleteFromRealm();
            }
            realm.commitTransaction();
            finish();
        } else if (view == descMoreLessTxt) {
            if (isDescExpand) {
                isDescExpand = false;
                descMoreLessTxt.setText("+" + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"));
                ViewGroup.LayoutParams vc = descWebView.getLayoutParams();
                vc.height = 140;
                descWebView.setLayoutParams(vc);
            } else {
                isDescExpand = true;
                descMoreLessTxt.setText("-" + generalFunc.retrieveLangLBl("", "LBL_LESS_TXT"));
                ViewGroup.LayoutParams vc = descWebView.getLayoutParams();
                vc.height = ViewGroup.LayoutParams.MATCH_PARENT;
                descWebView.setLayoutParams(vc);

            }

        }
    }

    public class WebViewTouchListener implements View.OnTouchListener {
        private float downX;

        @Override
        public boolean onTouch(final View v, final MotionEvent event) {
            if (event.getPointerCount() > 1) {
                //multi touch
                return true;
            }

            switch (event.getAction()) {
                case MotionEvent.ACTION_DOWN:
                    downX = event.getX();
                    break;
                case MotionEvent.ACTION_MOVE:
                case MotionEvent.ACTION_CANCEL:
                case MotionEvent.ACTION_UP:
                    // set x so that it doesn't move
                    event.setLocation(downX, event.getY());
                    break;
            }
            return false;
        }
    }

    @Override
    protected void onDestroy() {
        if (handler != null) {
            handler.removeCallbacksAndMessages(null);
        }
        super.onDestroy();
    }
}