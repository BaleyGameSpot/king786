package com.act;

import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Context;
import android.graphics.Color;
import android.os.Build;
import android.os.Bundle;
import android.util.Log;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.webkit.CookieManager;
import android.webkit.CookieSyncManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.fontanalyzer.SystemFont;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.WKWebView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 08-03-18.
 */

public class Help_DetailsActivity extends ParentActivity {

    MTextView titleTxt;
    ImageView backImgView;
    MTextView headerTitleTxt;
    MTextView descriptionTxt;
    MTextView contact_us_btn;
    MTextView textstillneedhelp;
    //main views//

    String required_str = "";
    String iHelpDetailId = "";
    String iUniqueId = "";
    ArrayList<HashMap<String, String>> reasonsDataList = new ArrayList<>();
    View contentAreaView;
    View loadingBar;
    Dialog FurtherAssistanceDialog;
    String responseStringWeb = "";
    private int filterPosition = -1;
    ImageView closeImg;
    WKWebView webView;

    MaterialEditText contentBox;
    MButton btn_type2;
    View helpContactslayout;
    MTextView categoryText;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_help_details);


        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        descriptionTxt = (MTextView) findViewById(R.id.descriptionTxt);
        headerTitleTxt = (MTextView) findViewById(R.id.headerTitleTxt);
        contact_us_btn = (MTextView) findViewById(R.id.contact_us_btn);
        textstillneedhelp = (MTextView) findViewById(R.id.textstillneedhelp);
        contentAreaView = findViewById(R.id.contentAreaView);
        loadingBar = findViewById(R.id.loadingBar);
        clearCookies(getApplication());
        webView = findViewById(R.id.webView);
        addToClickHandler(backImgView);
        addToClickHandler(contact_us_btn);

        if (getIntent().getStringExtra("iHelpDetailId") != null) {
            iHelpDetailId = getIntent().getStringExtra("iHelpDetailId");
        }

        if (getIntent().getStringExtra("iUniqueId") != null) {
            iUniqueId = getIntent().getStringExtra("iUniqueId");
        }

        setLabels();
        getCategoryTitleList();
    }

    @SuppressWarnings("deprecation")
    public static void clearCookies(Context context) {

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP_MR1) {
            Log.d("Api", "Using clearCookies code for API >=" + String.valueOf(Build.VERSION_CODES.LOLLIPOP_MR1));
            CookieManager.getInstance().removeAllCookies(null);
            CookieManager.getInstance().flush();
        } else {
            Log.d("Api", "Using clearCookies code for API <" + String.valueOf(Build.VERSION_CODES.LOLLIPOP_MR1));
            CookieSyncManager cookieSyncMngr = CookieSyncManager.createInstance(context);
            cookieSyncMngr.startSync();
            CookieManager cookieManager = CookieManager.getInstance();
            cookieManager.removeAllCookie();
            cookieManager.removeSessionCookie();
            cookieSyncMngr.stopSync();
            cookieSyncMngr.sync();
        }
    }

    public void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HEADER_HELP_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        contact_us_btn.setText("" + generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_HEADER_TXT"));
        textstillneedhelp.setText("" + generalFunc.retrieveLangLBl("", "LBL_STILL_NEED_HELP"));
    }

    public void getCategoryTitleList() {
        contentAreaView.setVisibility(View.GONE);
        loadingBar.setVisibility(View.VISIBLE);

        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getHelpDetail");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("appType", Utils.app_type);
        parameters.put("iUniqueId", iUniqueId);

        if (getIntent().hasExtra("iOrderId")) {
            parameters.put("iOrderId", getIntent().getStringExtra("iOrderId"));
            parameters.put("eSystem", Utils.eSystem_Type);
        } else if (getIntent().hasExtra("iBiddingPostId")) {
            parameters.put("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));
        } else {
            parameters.put("iTripId", getIntent().getStringExtra("iTripId"));
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    responseStringWeb = responseString;
                    generateFurtherAssistantDialog();
                    contentAreaView.setVisibility(View.VISIBLE);
                    loadingBar.setVisibility(View.GONE);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), true);
                }

            }
        });

    }

    @SuppressLint("ClickableViewAccessibility")
    private void buildReasonData(JSONArray obj_arr) {
        reasonsDataList.clear();
        for (int i = 0; i < obj_arr.length(); i++) {
            JSONObject obj_temp = generalFunc.getJsonObject(obj_arr, i);

            HashMap<String, String> mapData = new HashMap<>();
            mapData.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
            mapData.put("iHelpDetailId", generalFunc.getJsonValueStr("iHelpDetailId", obj_temp));
            mapData.put("tAnswer", generalFunc.getJsonValueStr("tAnswer", obj_temp));
            mapData.put("eShowFrom", generalFunc.getJsonValueStr("eShowFrom", obj_temp));
            mapData.put("vName", generalFunc.getJsonValueStr("vTitle", obj_temp));
            mapData.put("selectedSortValue", "");

            reasonsDataList.add(mapData);

            if (iHelpDetailId.equalsIgnoreCase(mapData.get("iHelpDetailId"))) {
                categoryText.setText(mapData.get("vTitle"));
                headerTitleTxt.setText(generalFunc.fromHtml(mapData.get("vTitle")));
                descriptionTxt.setText(generalFunc.fromHtml(mapData.get("tAnswer")));

                descriptionTxt.setVisibility(View.GONE);
                webView.loadData(mapData.get("tAnswer"));
                webView.setOnTouchListener((v, event) -> event.getPointerCount() > 1);

                if (mapData.get("eShowFrom").equalsIgnoreCase("Yes")) {
                    helpContactslayout.setVisibility(View.VISIBLE);
                    findViewById(R.id.help_card).setVisibility(View.VISIBLE);
                } else {
                    helpContactslayout.setVisibility(View.GONE);
                    findViewById(R.id.help_card).setVisibility(View.GONE);
                }
            }
        }

    }

    public void submitQuery() {
        boolean contentEntered = Utils.checkText(contentBox);

        if (!contentEntered) {
            ((MTextView) FurtherAssistanceDialog.findViewById(R.id.subjectBox_error)).setText(required_str);
            FurtherAssistanceDialog.findViewById(R.id.subjectBox_error).setVisibility(View.VISIBLE);

            return;
        } else {
            FurtherAssistanceDialog.findViewById(R.id.subjectBox_error).setVisibility(View.INVISIBLE);

        }

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "submitTripHelpDetail");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iHelpDetailId", iHelpDetailId);
        parameters.put("UserType", Utils.app_type);
        parameters.put("UserId", generalFunc.getMemberId());
        parameters.put("vComment", Utils.getText(contentBox));

        if (getIntent().hasExtra("iOrderId")) {
            parameters.put("iOrderId", getIntent().getStringExtra("iOrderId"));
            parameters.put("eSystem", Utils.eSystem_Type);
        } else if (getIntent().hasExtra("iBiddingPostId")) {
            parameters.put("iBiddingPostId", getIntent().getStringExtra("iBiddingPostId"));
        } else {
            parameters.put("TripId", getIntent().getStringExtra("iTripId"));
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    contentBox.setText("");
                }

                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), null);
                FurtherAssistanceDialog.dismiss();

            } else {
                generalFunc.showError();
            }
        });

    }

    public String getSelectCategoryText() {
        return ("" + generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT"));
    }

    public Context getActContext() {
        return Help_DetailsActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == R.id.backImgView) {
            Help_DetailsActivity.super.onBackPressed();
        } else if (i == R.id.contact_us_btn) {

            displayFurtherAssistantDialog();
            Utils.hideKeyboard(getActContext());

        } else if (i == btn_type2.getId()) {
            submitQuery();

        } else if (i == R.id.categoryarea) {
            openFilterDilaog();
        } else if (i == R.id.closeImg) {
            FurtherAssistanceDialog.findViewById(R.id.subjectBox_error).setVisibility(View.INVISIBLE);
            FurtherAssistanceDialog.dismiss();
        }
    }

    @SuppressLint("SetTextI18n")
    private void generateFurtherAssistantDialog() {

        FurtherAssistanceDialog = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        FurtherAssistanceDialog.setContentView(R.layout.furtherassistancedialog);

        MTextView contactTxt;
        MTextView reasonContactTxt;
        LinearLayout categoryarea;
        MTextView additionalCommentTxt;
        View pullIndicator;
        RelativeLayout contentBoxBorder;
        ImageView iv_arrow;

        closeImg = FurtherAssistanceDialog.findViewById(R.id.closeImg);
        helpContactslayout = FurtherAssistanceDialog.findViewById(R.id.helpContactslayout);
        contactTxt = (MTextView) FurtherAssistanceDialog.findViewById(R.id.contactTxt);
        reasonContactTxt = (MTextView) FurtherAssistanceDialog.findViewById(R.id.reasonContactTxt);
        categoryarea = (LinearLayout) FurtherAssistanceDialog.findViewById(R.id.categoryarea);
        categoryText = (MTextView) FurtherAssistanceDialog.findViewById(R.id.categoryText);
        iv_arrow = (ImageView) FurtherAssistanceDialog.findViewById(R.id.iv_arrow);
        pullIndicator = (View) FurtherAssistanceDialog.findViewById(R.id.pullIndicator);
        contentBoxBorder = (RelativeLayout) FurtherAssistanceDialog.findViewById(R.id.contentBoxBorder);
        additionalCommentTxt = (MTextView) FurtherAssistanceDialog.findViewById(R.id.additionalCommentTxt);
        contentBox = (MaterialEditText) FurtherAssistanceDialog.findViewById(R.id.contentBox);
        FurtherAssistanceDialog.findViewById(R.id.subjectBox_error).setVisibility(View.INVISIBLE);
        btn_type2 = ((MaterialRippleLayout) FurtherAssistanceDialog.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setId(Utils.generateViewId());


        addToClickHandler(btn_type2);
        addToClickHandler(categoryarea);
        addToClickHandler(closeImg);

        new CreateRoundedView(Color.parseColor("#949494"), 15, 0, Color.parseColor("#949494"), pullIndicator);
        new CreateRoundedView(Color.parseColor("#ffffff"), 1, 1, Color.parseColor("#949494"), contentBoxBorder);
        new CreateRoundedView(Color.parseColor("#ffffff"), 1, 1, Color.parseColor("#949494"), categoryarea);

        reasonContactTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RES_TO_CONTACT"));//LBL_SELECT_RES_TO_CONTACT
        contactTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_SUPPORT_ASSISTANCE_TXT"));
        additionalCommentTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_COMMENTS"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_SUBMIT_TXT")); //LBL_SEND_QUERY_BTN_TXT
        contentBox.setHint(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_WRITE_EMAIL_TXT"));
        // contentBox.setFloatingLabelAlwaysShown(true);
        MyUtils.editBoxMultiLine(contentBox);
        contentBox.setHideUnderline(true);

        contentBox.setTypeface(SystemFont.FontStyle.DEFAULT.font);

        if (!getIntent().hasExtra("iOrderId")) {
            new CreateRoundedView(Color.parseColor("#FFFFFF"), Utils.dipToPixels(getActContext(), 2), Utils.dipToPixels(getActContext(), 1), Color.parseColor("#989898"), categoryarea);
        }

        buildReasonData(generalFunc.getJsonArray(Utils.message_str, responseStringWeb));

        if (reasonsDataList.size() == 1) {
            categoryarea.setClickable(false);
            iv_arrow.setVisibility(View.GONE);
        }
    }

    private void displayFurtherAssistantDialog() {
        Window window = FurtherAssistanceDialog.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.FILL_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        FurtherAssistanceDialog.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        FurtherAssistanceDialog.show();
        Utils.hideKeyboard(getActContext());


    }


    public void openFilterDilaog() {
        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", getSelectCategoryText()), reasonsDataList, OpenListView.OpenDirection.CENTER, true, position -> {
            filterPosition = position;
            categoryText.setText(reasonsDataList.get(position).get("vName"));
        }).show(filterPosition, "vName");
    }

}
