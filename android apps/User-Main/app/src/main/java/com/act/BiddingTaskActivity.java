package com.act;

import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.graphics.Paint;
import android.os.Bundle;
import android.text.InputType;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.BiddingReceivedRecycleAdapter;
import com.dialogs.OpenListView;
import com.fontanalyzer.SystemFont;
import com.general.SkeletonViewHandler;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.WKWebView;
import com.view.editBox.MaterialEditText;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class BiddingTaskActivity extends ParentActivity implements BiddingReceivedRecycleAdapter.OnItemClickListener {

    MTextView titleTxt, serviceNameTxt, attachedMediaTxt, commentHTxt, providerTitle, dateTxt;
    MTextView bidAmountHtxt, bidAmountVtxt, commentVTxt, statusMsgTxt, finalHtxt, finalAmountVtxt;
    public ImageView backImgView, refreshImgView;
    RecyclerView providersRecyclerView;
    BiddingReceivedRecycleAdapter biddingReceivedRecycleAdapter;
    ArrayList<HashMap<String, String>> providerList;
    String next_page_str = "", LBL_ACCEPT_TXT, LBL_DECLINE_TXT, LBL_CONFIRM_TXT, LB_REWISE_OFFER;
    private String iBiddingPostId = "", required_str = "", eWalletIgnore = "No";
    boolean isNextPageAvailable = false, mIsLoading = false, isDetailsView = false;
    ProgressBar loader;
    NestedScrollView nestedScroll;
    View finalamountArea, gradientArea, headingArea;
    private static final int WEBVIEWPAYMENT = 001;
    private AlertDialog mReOfferDialog, declineAlertDialog, dialog_declineOrder;
    int selPos = -1, selCurrentPosition = -1;

    LinearLayout contentArea;
    //manage Outstanding.
    String ShowAdjustTripBtn;
    String ShowPayNow;
    String ShowContactUsBtn;
    androidx.appcompat.app.AlertDialog outstanding_dialog;
    private ServerTask currentWebTask;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_bidding_task);

        initView();
        setLabel();

        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(contentArea, R.layout.skeleton_task_detail);

        getProviderList(false);
    }

    private void initView() {
        providerList = new ArrayList<>();
        isDetailsView = getIntent().getBooleanExtra("isDetailsView", false);
        iBiddingPostId = getIntent().getStringExtra("iBiddingPostId");

        gradientArea = findViewById(R.id.gradientArea);
        manageVectorImage(gradientArea, R.drawable.ic_gradient, R.drawable.ic_gradient_compat);

        titleTxt = findViewById(R.id.titleTxt);
        finalHtxt = findViewById(R.id.finalHtxt);
        finalAmountVtxt = findViewById(R.id.finalAmountVtxt);
        finalamountArea = findViewById(R.id.finalamountArea);
        nestedScroll = findViewById(R.id.nestedScroll);
        headingArea = findViewById(R.id.headingArea);
        headingArea.setVisibility(View.INVISIBLE);
        bidAmountHtxt = findViewById(R.id.bidAmountHtxt);
        bidAmountVtxt = findViewById(R.id.bidAmountVtxt);
        commentVTxt = findViewById(R.id.commentVTxt);
        statusMsgTxt = findViewById(R.id.statusMsgTxt);
        serviceNameTxt = findViewById(R.id.serviceNameTxt);
        attachedMediaTxt = findViewById(R.id.attachedMediaTxt);
        commentHTxt = findViewById(R.id.commentHTxt);
        loader = findViewById(R.id.loader);
        providerTitle = findViewById(R.id.providerTitle);
        providerTitle.setVisibility(View.INVISIBLE);
        dateTxt = findViewById(R.id.dateTxt);
        backImgView = findViewById(R.id.backImgView);
        refreshImgView = findViewById(R.id.refreshImgView);
        providersRecyclerView = findViewById(R.id.providersRecyclerView);
        contentArea = findViewById(R.id.contentArea);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        addToClickHandler(refreshImgView);
        biddingReceivedRecycleAdapter = new BiddingReceivedRecycleAdapter(getActContext(), providerList, generalFunc, false);
        biddingReceivedRecycleAdapter.setOnItemClickListener(this);
        providersRecyclerView.setAdapter(biddingReceivedRecycleAdapter);
        providersRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NotNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);

                int visibleItemCount = Objects.requireNonNull(recyclerView.getLayoutManager()).getChildCount();
                int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {

                    mIsLoading = true;
                    biddingReceivedRecycleAdapter.addFooterView();
                    providersRecyclerView.stopScroll();
                    recyclerView.post(() -> biddingReceivedRecycleAdapter.notifyDataSetChanged());
                    getProviderList(true);

                } else if (!isNextPageAvailable) {
                    biddingReceivedRecycleAdapter.removeFooterView();
                }
            }
        });
    }

    private void setLabel() {
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TASK_DETAILS_OFFERS_TXT"));
        attachedMediaTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ATTACHED_MEDIA_FILES_TEXT"));
        commentHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DETAILS"));
        providerTitle.setText(generalFunc.retrieveLangLBl("", "LBL_BID_RECEIVED"));
        LBL_ACCEPT_TXT = generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT");
        LBL_DECLINE_TXT = generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT");
        LBL_CONFIRM_TXT = generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT");
        LB_REWISE_OFFER = generalFunc.retrieveLangLBl("Rewise offer", "LBL_REWISE_OFFER");
        bidAmountHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_TASK_BUDGET_TXT"));
    }

    private void setData(String responsString) {

        serviceNameTxt.setText(generalFunc.getJsonValue("vServiceName", responsString));
        bidAmountVtxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("fBiddingAmount", responsString)));
        finalAmountVtxt.setText(generalFunc.getJsonValue("biddingFinalAmount", responsString));
        finalHtxt.setText(generalFunc.getJsonValue("biddingfinalAmountTitle", responsString));

        if (generalFunc.getJsonValue("isMediaUploaded", responsString).equalsIgnoreCase("Yes")) {
            attachedMediaTxt.setVisibility(View.VISIBLE);
            addToClickHandler(attachedMediaTxt);
            attachedMediaTxt.setPaintFlags(Paint.UNDERLINE_TEXT_FLAG);
        } else {
            attachedMediaTxt.setVisibility(View.GONE);
        }
        commentHTxt.setVisibility(View.GONE);
        if (generalFunc.getJsonValue("tDescription", responsString) != null && !generalFunc.getJsonValue("tDescription", responsString).equalsIgnoreCase("")) {
            commentVTxt.setText(GeneralFunctions.fromHtml(generalFunc.getJsonValue("tDescription", responsString)));
            generalFunc.makeTextViewResizable(commentVTxt, 2, "...\n+ " + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);
            commentVTxt.setVisibility(View.VISIBLE);
        } else {
            commentVTxt.setVisibility(View.GONE);
        }

        if (generalFunc.getJsonValue("cancelReason", responsString) != null && !generalFunc.getJsonValue("cancelReason", responsString).equalsIgnoreCase("")) {
            statusMsgTxt.setText(generalFunc.getJsonValue("cancelReason", responsString));
            statusMsgTxt.setVisibility(View.VISIBLE);
            refreshImgView.setVisibility(View.GONE);
        } else if (generalFunc.getJsonValue("eStatus", responsString) != null && generalFunc.getJsonValue("eStatus", responsString).equalsIgnoreCase("Accepted")) {
            refreshImgView.setVisibility(View.GONE);
        } else {
            statusMsgTxt.setVisibility(View.GONE);
            refreshImgView.setVisibility(View.VISIBLE);
        }
        //dateTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dBiddingDate", responsString), Utils.OriginalDateFormate, DateTimeUtils.DateTimeFormat)));
        dateTxt.setText(generalFunc.getJsonValue("tDisplayDateTime", responsString));
    }

    public void getProviderList(final boolean isLoadMore) {

        if (loader.getVisibility() != View.VISIBLE && !isLoadMore) {
            loader.setVisibility(View.VISIBLE);
        }
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getBiddingPostInfo");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iBiddingPostId", iBiddingPostId);

        Logger.d("next_page_str", ":" + next_page_str);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }

        if (currentWebTask != null) {
            currentWebTask.cancel(true);
            currentWebTask = null;
        }
        currentWebTask = ApiHandler.execute(getActContext(), parameters, responseString -> {
            closeLoader();
            headingArea.setVisibility(View.VISIBLE);
            if (!isDetailsView) {

                providerTitle.setVisibility(View.VISIBLE);
                providersRecyclerView.setVisibility(View.VISIBLE);
            } else {
                finalamountArea.setVisibility(View.VISIBLE);
            }
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (currentWebTask != null) {
                        currentWebTask.cancel(true);
                        currentWebTask = null;
                    }
                    String nextPage = generalFunc.getJsonValue("NextPage", responseString);


                    setData(generalFunc.getJsonValue(Utils.message_str, responseString));
                    JSONArray arr_rides = generalFunc.getJsonArray("provider", generalFunc.getJsonValue(Utils.message_str, responseString));


                    if (arr_rides != null && arr_rides.length() > 0) {
                        for (int i = 0; i < arr_rides.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr_rides, i);

                            HashMap<String, String> map = new HashMap<>();

                            map.put("eJobType", generalFunc.getJsonValueStr("eJobType", obj_temp));

                            map.put("iDriverId", generalFunc.getJsonValueStr("iDriverId", obj_temp));
                            map.put("vAvgRating", generalFunc.getJsonValueStr("vAvgRating", obj_temp));
                            map.put("vBiddingPostNo", generalFunc.getJsonValueStr("vBiddingPostNo", obj_temp));
                            map.put("tDateOrig", generalFunc.getJsonValueStr("tDateOrig", obj_temp));
                            map.put("vName", generalFunc.getJsonValueStr("vName", obj_temp));
                            map.put("vImage", generalFunc.getJsonValueStr("vImage", obj_temp));
                            map.put("IsDriverMediaUploaded", generalFunc.getJsonValueStr("IsDriverMediaUploaded", obj_temp));
                            map.put("showConfirmBtn", generalFunc.getJsonValueStr("showConfirmBtn", obj_temp));
                            map.put("showDeclineBtn", generalFunc.getJsonValueStr("showDeclineBtn", obj_temp));
                            map.put("showAcceptBtn", generalFunc.getJsonValueStr("showAcceptBtn", obj_temp));
                            map.put("biddingAmount", generalFunc.getJsonValueStr("biddingAmount", obj_temp));
                            map.put("biddingAmountTitle", generalFunc.getJsonValueStr("biddingAmountTitle", obj_temp));
                            map.put("biddingReofferAmountTitle", generalFunc.getJsonValueStr("biddingReofferAmountTitle", obj_temp));
                            map.put("biddingReofferAmount", generalFunc.getJsonValueStr("biddingReofferAmount", obj_temp));
                            map.put("iBiddingPostId", generalFunc.getJsonValueStr("iBiddingPostId", obj_temp));
                            map.put("dLUpdateDate", generalFunc.getJsonValueStr("dLUpdateDate", obj_temp));
                            map.put("eStatusMsg", generalFunc.getJsonValueStr("eStatusMsg", obj_temp));
                            map.put("showReofferBtn", generalFunc.getJsonValueStr("showReofferBtn", obj_temp));
                            map.put("biddingfinalAmountTitle", generalFunc.getJsonValueStr("biddingfinalAmountTitle", obj_temp));
                            map.put("biddingFinalAmount", generalFunc.getJsonValueStr("biddingFinalAmount", obj_temp));
                            map.put("biddingConfirmAmount", generalFunc.getJsonValueStr("biddingConfirmAmount", obj_temp));
                            map.put("description_user", generalFunc.getJsonValueStr("description_user", obj_temp));
                            map.put("description_driver", generalFunc.getJsonValueStr("description_driver", obj_temp));
                            map.put("JSON", obj_temp.toString());
                            map.put("LBL_ACCEPT_TXT", LBL_ACCEPT_TXT);
                            map.put("LBL_DECLINE_TXT", LBL_DECLINE_TXT);
                            map.put("LBL_CONFIRM_TXT", LBL_CONFIRM_TXT);
                            map.put("LB_REWISE_OFFER", LB_REWISE_OFFER);

                            providerList.add(map);

                        }
                    } else {
                        providerTitle.setVisibility(View.GONE);
                    }

                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }


                }
                if (biddingReceivedRecycleAdapter != null) {
                    biddingReceivedRecycleAdapter.notifyDataSetChanged();
                } else {
                    manageAdapter();
                }
            } else {
                if (!isLoadMore) {
                    removeNextPageConfig();
                }
            }
            SkeletonViewHandler.getInstance().hideSkeletonView();
            mIsLoading = false;
        });

    }

    private void manageAdapter() {
        biddingReceivedRecycleAdapter = new BiddingReceivedRecycleAdapter(getActContext(), providerList, generalFunc, false);
        biddingReceivedRecycleAdapter.setOnItemClickListener(this);
        providersRecyclerView.setAdapter(biddingReceivedRecycleAdapter);
    }

    private void closeLoader() {
        if (loader.getVisibility() == View.VISIBLE) {
            loader.setVisibility(View.GONE);
        }
    }

    private void removeNextPageConfig() {

        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        if (biddingReceivedRecycleAdapter != null) {
            biddingReceivedRecycleAdapter.removeFooterView();
        }
    }

    private Context getActContext() {
        return BiddingTaskActivity.this;
    }

    @Override
    public void onItemClickList(View v, int position, String type) {

        selPos = position;
        if (type.equalsIgnoreCase("accept") || type.equalsIgnoreCase("confirm")) {
            if (generalFunc.getJsonValueStr("ENABLE_TAX_FOR_BIDDING_SERVICE", obj_userProfile).equalsIgnoreCase("Yes")) {
                generateTaxDetailsForService(position);
            } else {
                ProceedToConfirm();
            }
        } else if (type.equalsIgnoreCase("chat")) {
            manageChat(position);

        } else if (type.equalsIgnoreCase("offer")) {
            reOfferDialog(position);
        } else if (type.equalsIgnoreCase("decline")) {
            if (Objects.requireNonNull(providerList.get(position).get("showReofferBtn")).equalsIgnoreCase("Yes")) {
                declineTaskDialog(position);
            } else {
                showDeclineReasonsAlert(position);
            }


        } else if (type.equalsIgnoreCase("bidMedia")) {
            HashMap<String, String> hashMap = providerList.get(position);
            Bundle bn = new Bundle();
            bn.putString("iDriverId", hashMap.get("iDriverId"));
            new ActUtils(getActContext()).startActWithData(BiddingAdditionalMediaActivity.class, bn);
        }


    }

    private void generateTaxDetailsForService(int position) {
        HashMap<String, String> hashMap = providerList.get(position);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "PaymentSummeryBidding");
        parameters.put("iBiddingPostId", hashMap.get("iBiddingPostId"));
        parameters.put("iDriverId", hashMap.get("iDriverId"));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            if (Utils.checkText(responseString)) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail) {
                    addFareDetailLayout(responseString);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void addFareDetailLayout(String responseString) {
        JSONArray bidding_fare_arr = generalFunc.getJsonArray("bidding_fare_arr", responseString);
        BottomSheetDialog dialog = new BottomSheetDialog(getActContext());
        View contentView = View.inflate(getActContext(), R.layout.bill_details_bidding, null);

        dialog.setContentView(contentView);

        MTextView billTitleTxt = dialog.findViewById(R.id.billTitleTxt);
        ImageView cancelImage = dialog.findViewById(R.id.imageCancel);
        LinearLayout containerView = dialog.findViewById(R.id.containerView);
        MButton btn_type_proceed = ((MaterialRippleLayout) Objects.requireNonNull(contentView.findViewById(R.id.btn_type_proceed))).getChildView();
        btn_type_proceed.setId(Utils.generateViewId());
        assert billTitleTxt != null;
        billTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INVOICE_DETAILS"));
        btn_type_proceed.setText(generalFunc.retrieveLangLBl("", "LBL_PAY_TXT"));

        assert containerView != null;
        addDetailsLayout(bidding_fare_arr, containerView);

        assert cancelImage != null;
        cancelImage.setOnClickListener(view -> {
            if (dialog.isShowing()) {
                dialog.dismiss();
            }
        });

        BottomSheetBehavior<View> mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(1500);
        mBehavior.setHideable(false);
        mBehavior.setState(BottomSheetBehavior.STATE_EXPANDED);

        btn_type_proceed.setOnClickListener(view -> {
            if (dialog.isShowing()) {
                dialog.dismiss();
            }
            ProceedToConfirm();

        });

        View bottomSheetView = dialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        dialog.setCancelable(true);
        dialog.setCanceledOnTouchOutside(true);
        LayoutDirection.setLayoutDirection(dialog);
        dialog.show();
    }

    private void ProceedToConfirm() {
        Bundle bn = new Bundle();
        bn.putBoolean("handleResponse", true);
        String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", obj_userProfile) + "&eType=" + "Bidding";
        url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
        url = url + "&GeneralUserType=" + Utils.app_type;
        url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
        url = url + "&ePaymentOption=" + "Card";
        url = url + "&vPayMethod=" + "Instant";
        url = url + "&SYSTEM_TYPE=" + "APP";
        url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();


        bn.putString("url", url);
        bn.putBoolean("handleResponse", true);
        bn.putBoolean("isBack", false);
        new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
    }

    private void addDetailsLayout(JSONArray bidding_fare_arr, LinearLayout containerView) {
        if (containerView.getChildCount() > 0) {
            containerView.removeAllViewsInLayout();
        }
        for (int i = 0; i < bidding_fare_arr.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(bidding_fare_arr, i);
            try {
                String rName = Objects.requireNonNull(jobject.names()).getString(0);
                String rValue = jobject.get(rName).toString();
                containerView.addView(addFareDetailRow(rName, rValue, (bidding_fare_arr.length() - 1) == i));
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }
    }

    private View addFareDetailRow(String row_name, String row_value, boolean isLast) {
        View convertView = null;
        if (row_name.equalsIgnoreCase("eDisplaySeperator")) {
            convertView = new View(getActContext());
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, Utils.dipToPixels(getActContext(), 1));
            params.setMargins((int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._5sdp), (int) getResources().getDimension(R.dimen._10sdp), (int) getResources().getDimension(R.dimen._5sdp));
            convertView.setBackgroundColor(Color.parseColor("#dedede"));
            convertView.setLayoutParams(params);
        } else {
            LayoutInflater infalInflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            convertView = infalInflater.inflate(R.layout.bill_item_genie, null);

            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            params.setMargins(0, isLast ? (int) getResources().getDimension(R.dimen._5sdp) : (int) getResources().getDimension(R.dimen._10sdp), 0, isLast ? (int) getResources().getDimension(R.dimen._5sdp) : 0);
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
        return convertView;
    }

    private void declineTaskDialog(int pos) {
        if (declineAlertDialog != null && declineAlertDialog.isShowing()) {
            return;
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.desgin_decline_task, null);
        builder.setView(dialogView);

        MTextView txtReOffer = dialogView.findViewById(R.id.txtReOffer);
        MTextView txtDecline = dialogView.findViewById(R.id.txtDecline);
        MTextView titileTxt = dialogView.findViewById(R.id.titileTxt);
        WKWebView msgTxt = dialogView.findViewById(R.id.msgTxt);
        titileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TASK"));
        txtReOffer.setText(generalFunc.retrieveLangLBl("", "LBL_RE_OFFER_TXT"));
        txtDecline.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT"));
        msgTxt.loadData(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TASK_CONFIRM_MSG"), WKWebView.ContentType.ALERT_DIALOG);

        txtReOffer.setOnClickListener(view -> {
            declineAlertDialog.dismiss();
            reOfferDialog(pos);
        });
        txtDecline.setOnClickListener(view -> {
            declineAlertDialog.dismiss();
            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                generateAlert.closeAlertBox();
                if (btn_id == 1) {
                    showDeclineReasonsAlert(pos);
                }
            });
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_DECLINE_BIDDING_TASK_TXT"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
            generateAlert.showAlertBox();
        });
        declineAlertDialog = builder.create();
        declineAlertDialog.setCancelable(true);
        LayoutDirection.setLayoutDirection(declineAlertDialog);
        declineAlertDialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        declineAlertDialog.show();
    }

    private void reOfferDialog(int pos) {
        if (mReOfferDialog != null && mReOfferDialog.isShowing()) {
            return;
        }
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dialog_bidding_re_offre, null);
        builder.setView(dialogView);


        final MaterialEditText editBoxReOfferAmount = dialogView.findViewById(R.id.editBoxReOfferAmount);
        editBoxReOfferAmount.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        editBoxReOfferAmount.setHint(generalFunc.retrieveLangLBl("", "LBL_YOUR_OFFER_TXT"));
        editBoxReOfferAmount.setHideUnderline(true);
        editBoxReOfferAmount.setGravity(Gravity.START | Gravity.CENTER);
        editBoxReOfferAmount.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        editBoxReOfferAmount.setIncludeFontPadding(false);
        editBoxReOfferAmount.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.border));
        if (generalFunc.isRTLmode()) {
            editBoxReOfferAmount.setPaddings((int) getResources().getDimension(R.dimen._5sdp), 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            editBoxReOfferAmount.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, (int) getResources().getDimension(R.dimen._5sdp), 0);
        }

        final MTextView txtTitle = dialogView.findViewById(R.id.txtTitle);
        txtTitle.setText(generalFunc.retrieveLangLBl("", "LBL_RE_OFFER_TXT"));

        final MaterialEditText editBoxDescription = dialogView.findViewById(R.id.editBoxDescription);
        editBoxDescription.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        editBoxDescription.setSingleLine(false);
        editBoxDescription.setHideUnderline(true);
        editBoxDescription.setGravity(Gravity.START | Gravity.TOP);
        editBoxDescription.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        editBoxDescription.setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.border));
        if (generalFunc.isRTLmode()) {
            editBoxDescription.setPaddings((int) getResources().getDimension(R.dimen._5sdp), 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            editBoxDescription.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, (int) getResources().getDimension(R.dimen._5sdp), 0);
        }
        editBoxDescription.setIncludeFontPadding(false);
        editBoxDescription.setHint(generalFunc.retrieveLangLBl("", "LBL_DESCRIPTION"));

        final MTextView txtOk = dialogView.findViewById(R.id.txtOk);
        txtOk.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        txtOk.setOnClickListener(view -> {
            boolean reOfferAmount = Utils.checkText(editBoxReOfferAmount) || Utils.setErrorFields(editBoxReOfferAmount, required_str);
            if (!reOfferAmount) {
                return;
            }
            Utils.hideKeyboard(getActContext());
            mReOfferDialog.dismiss();
            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
            generateAlert.setCancelable(false);
            generateAlert.setBtnClickList(btn_id -> {
                generateAlert.closeAlertBox();
                if (btn_id == 1) {
                    confirmBidding(providerList.get(pos).get("iDriverId"), "Reoffer", "", Utils.getText(editBoxReOfferAmount), Utils.getText(editBoxDescription), false);
                }
            });
            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_RE_OFFER_BIDDING_TASK_TXT"));
            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
            generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
            generateAlert.showAlertBox();
        });

        final MTextView txtCancel = dialogView.findViewById(R.id.txtCancel);
        txtCancel.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        txtCancel.setOnClickListener(view -> mReOfferDialog.dismiss());

        mReOfferDialog = builder.create();
        mReOfferDialog.setCancelable(true);
        LayoutDirection.setLayoutDirection(mReOfferDialog);
        mReOfferDialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        mReOfferDialog.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_ADJUST_RESIZE);
        mReOfferDialog.show();
    }


    private void manageChat(int pos) {
        HashMap<String, String> hashMap = providerList.get(pos);
        MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                .setToMemberId(hashMap.get("iDriverId"))
                .setMedia(CommunicationManager.MEDIA_TYPE)
                .setTripId(hashMap.get("iBiddingPostId"))
                .setBookingNo(hashMap.get("vBiddingPostNo"))
                .setBid(true)
                .build();
        CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.CHAT);
    }

    private void showDeclineReasonsAlert(int pos) {
        if (dialog_declineOrder != null) {
            if (dialog_declineOrder.isShowing()) {
                dialog_declineOrder.dismiss();
            }
            dialog_declineOrder = null;
        }
        selCurrentPosition = -1;

        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        int size10sdp = (int) getResources().getDimension(R.dimen._10sdp);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, size10sdp, 0);
        } else {
            reasonBox.setPaddings(size10sdp, 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));

        ArrayList<HashMap<String, String>> sub_list = new ArrayList<>();
        MTextView cancelTxt = dialogView.findViewById(R.id.cancelTxt);
        MTextView submitTxt = dialogView.findViewById(R.id.submitTxt);
        MTextView subTitleTxt = dialogView.findViewById(R.id.subTitleTxt);
        ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        subTitleTxt.setText(generalFunc.retrieveLangLBl("Cancel Bidding", "LBL_DECLINE_TASK"));

        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));
        MTextView declinereasonBox = dialogView.findViewById(R.id.declinereasonBox);
        declinereasonBox.setText("-- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_CANCEL_REASON") + " --");
        submitTxt.setClickable(false);
        submitTxt.setTextColor(getResources().getColor(R.color.gray_holo_light));

        submitTxt.setOnClickListener(v -> {
            if (selCurrentPosition == -1) {
                return;
            }
            if (!Utils.checkText(reasonBox) && selCurrentPosition == (sub_list.size() - 1)) {
                errorTextView.setVisibility(View.VISIBLE);
                errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                return;
            }
            confirmBidding(providerList.get(pos).get("iDriverId"), "Decline", sub_list.get(selCurrentPosition).get("id"), "", Utils.getText(reasonBox), false);
            dialog_declineOrder.dismiss();
        });
        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });
        cancelImg.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });
        declinereasonBox.setOnClickListener(v -> {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "GetCancelReasons");
            parameters.put("iMemberId", generalFunc.getMemberId());
            parameters.put("eUserType", Utils.app_type);
            parameters.put("eJobType", providerList.get(pos).get("eJobType"));

            parameters.put("iBiddingPostId", providerList.get(pos).get("iBiddingPostId"));

            ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
                sub_list.clear();
                if (Utils.checkText(responseString)) {

                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
                        if (arr_msg != null) {
                            int arrSize = arr_msg.length();
                            for (int i = 0; i < arrSize; i++) {
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
                                submitTxt.setTextColor(getResources().getColor(R.color.white));
                            }).show(selCurrentPosition, "title");
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
                        }
                    } else {
                        String message = generalFunc.getJsonValue(Utils.message_str, responseString);
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

    public void outstandingDialog(/*boolean isReqNow*/String responseString) {

        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dailog_outstanding, null);

        final MTextView outStandingTitle = (MTextView) dialogView.findViewById(R.id.outStandingTitle);
        final MTextView outStandingValue = (MTextView) dialogView.findViewById(R.id.outStandingValue);
        final MTextView cardtitleTxt = (MTextView) dialogView.findViewById(R.id.cardtitleTxt);
        final MTextView adjustTitleTxt = (MTextView) dialogView.findViewById(R.id.adjustTitleTxt);

        final LinearLayout cardArea = (LinearLayout) dialogView.findViewById(R.id.cardArea);
        final LinearLayout adjustarea = (LinearLayout) dialogView.findViewById(R.id.adjustarea);
        final MTextView adjustSubTitleTxt = dialogView.findViewById(R.id.adjustSubTitleTxt);
        final MTextView adjustTripMessageTxt = dialogView.findViewById(R.id.adjustTripMessageTxt);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.imgCardPayNow)).setRotationY(180);
            (dialogView.findViewById(R.id.imgAdjustInTrip)).setRotationY(180);
        }

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        outStandingTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUTSTANDING_AMOUNT_TXT"));

        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in your Task", "LBL_ADJUST_OUT_AMT_TASK_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));

        outStandingValue.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("fOutStandingAmountWithSymbol", responseString)));
        cardtitleTxt.setText(generalFunc.retrieveLangLBl("Pay Now", "LBL_PAY_NOW"));

        ShowAdjustTripBtn = generalFunc.getJsonValue("ShowAdjustTripBtn", responseString);
        ShowAdjustTripBtn = (ShowAdjustTripBtn == null || ShowAdjustTripBtn.isEmpty()) ? "No" : ShowAdjustTripBtn;
        ShowPayNow = generalFunc.getJsonValue("ShowPayNow", responseString);
        ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", responseString);
        ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;
        ShowPayNow = (ShowPayNow == null || ShowPayNow.isEmpty()) ? "No" : ShowPayNow;


        if (ShowPayNow.equalsIgnoreCase("Yes") && ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.VISIBLE);
        } else if (ShowPayNow.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        } else if (ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            /*String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;
            }*/
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                adjustTripMessageTxt.setText(outstanding_restriction_label);
            }
        }
        /*else if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {

            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 1) {
                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                    }
                });
                generateAlert.setContentMessage("", outstanding_restriction_label);
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_OK"));
                if (ShowContactUsBtn != null && ShowContactUsBtn.equalsIgnoreCase("Yes")) {
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                }
                generateAlert.showAlertBox();
                return;
            }
        }*/

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(ContactUsActivity.class));
            /*if (generalFunc.isRTLmode()) {
                (dialogView.findViewById(R.id.contactUsArrow)).setRotationY(180);
            }*/
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();

            Bundle bn = new Bundle();
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", obj_userProfile) + "&eType=" + "Bidding" + "&ePaymentType=ChargeOutstandingAmount";


            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();


            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);
            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
        });

        adjustarea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            /*String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);


            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {

                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;

            }*/

            confirmBidding(providerList.get(selPos).get("iDriverId"), "Accepted", "", "", "", true);
        });

        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);

        btn_type2.setOnClickListener(v -> outstanding_dialog.dismiss());

        builder.setView(dialogView);
        outstanding_dialog = builder.create();
        LayoutDirection.setLayoutDirection(outstanding_dialog);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.cardimagearrow)).setRotationY(180);
            (dialogView.findViewById(R.id.adjustimagearrow)).setRotationY(180);
        }
        outstanding_dialog.setCancelable(false);
        outstanding_dialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    private void confirmBidding(String iDriverId, String eStatus, String iCancelReasonId, String amount, String reason, boolean isOutStandingAdjusted) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateBiddingStatus");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iBiddingPostId", iBiddingPostId);
        parameters.put("iDriverId", iDriverId);

        parameters.put("eWalletIgnore", eWalletIgnore);

        parameters.put("eStatus", eStatus);
        if (eStatus.equalsIgnoreCase("Decline")) {
            parameters.put("iCancelReasonId", iCancelReasonId);
            parameters.put("vCancelReason", reason);
        }
        if (eStatus.equalsIgnoreCase("Reoffer")) {
            parameters.put("amount", amount);
            parameters.put("description", reason);
        }

        if (isOutStandingAdjusted) {
            parameters.put("isOutStandingAdjusted", "Yes");
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                String message_str = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {


                    String fOutStandingAmount = generalFunc.getJsonValue("fOutStandingAmount", responseString);

                    if (GeneralFunctions.parseDoubleValue(0.0, fOutStandingAmount) > 0) {
                        outstandingDialog(responseString);
                        return;
                    }

                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message_str), buttonId -> {
                        providersRecyclerView.setVisibility(View.GONE);
                        providerList.clear();
                        getProviderList(false);
                    });
                } else {
                    if (message_str.equalsIgnoreCase("LOW_WALLET_AMOUNT")) {

                        String walletMsg;
                        String low_balance_content_msg = generalFunc.getJsonValue("low_balance_content_msg", responseString);

                        if (low_balance_content_msg != null && !low_balance_content_msg.equalsIgnoreCase("")) {
                            walletMsg = low_balance_content_msg;
                        } else {
                            walletMsg = generalFunc.retrieveLangLBl("", "LBL_WALLET_LOW_AMOUNT_MSG_TXT");
                        }

                        String LBL_CANCEL_TXT = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT");
                        String IS_RESTRICT_TO_WALLET_AMOUNT = generalFunc.getJsonValue("IS_RESTRICT_TO_WALLET_AMOUNT", responseString);

                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"), walletMsg, IS_RESTRICT_TO_WALLET_AMOUNT.equalsIgnoreCase("No") ? generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN") : LBL_CANCEL_TXT, generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"), IS_RESTRICT_TO_WALLET_AMOUNT.equalsIgnoreCase("NO") ? LBL_CANCEL_TXT : "", button_Id -> {
                            if (button_Id == 1) {
                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            } else if (button_Id == 0) {
                                if (IS_RESTRICT_TO_WALLET_AMOUNT.equalsIgnoreCase("No")) {
                                    eWalletIgnore = "Yes";
                                    confirmBidding(providerList.get(selPos).get("iDriverId"), "Accepted", "", "", "", false);
                                }
                            }
                        });
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message_str));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            BiddingTaskActivity.super.onBackPressed();
        } else if (i == R.id.refreshImgView) {
            providerList.clear();
            biddingReceivedRecycleAdapter = null;
            providersRecyclerView.setVisibility(View.GONE);
            getProviderList(false);

        } else if (i == attachedMediaTxt.getId()) {
            Bundle bn = new Bundle();
            bn.putString("iBiddingPostId", iBiddingPostId);
            new ActUtils(getActContext()).startActWithData(BiddingAdditionalMediaActivity.class, bn);
        }
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {
            confirmBidding(providerList.get(selPos).get("iDriverId"), "Accepted", "", "", "", false);
        }
    }
}