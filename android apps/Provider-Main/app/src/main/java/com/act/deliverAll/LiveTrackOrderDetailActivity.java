package com.act.deliverAll;

import android.annotation.SuppressLint;
import android.app.Dialog;
import android.content.Context;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.location.Location;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.Html;
import android.text.InputType;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.ScrollView;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.Toolbar;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.ContactUsActivity;
import com.activity.ParentActivity;
import com.adapter.files.MoreInstructionAdapter;
import com.adapter.files.deliverAll.OrderItemListRecycleAdapter;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.PreferenceDailogJava;
import com.general.files.UploadProfileImage;
import com.buddyverse.providers.R;
import com.kyleduo.switchbutton.SwitchButton;
import com.model.deliverAll.orderDetailDataModel;
import com.model.deliverAll.orderItemDetailDataModel;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.LoadImageGlide;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.apache.commons.lang3.StringUtils;
import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 17-04-18.
 */

public class LiveTrackOrderDetailActivity extends ParentActivity implements OrderItemListRecycleAdapter.OnItemClickListener, GetLocationUpdates.LocationUpdatesListener {
    MTextView titleTxt;
    MTextView noSItemsTxt, orderIdHTxt, orderIdVTxt, orderStatusTxt, orderDateTxt, orderTimeVTxt;
    MTextView orderTotalBillHTxt, orderTotalBillVTxt, collectAmountRestHTxt, collectAmountRestVTxt, collectAmountUserHTxt, collectAmountUserVTxt;
    MTextView userNameVTxt, userAddressTxt, restaurantLocationVTxt, distanceHTxt, distanceVTxt;
    ImageView backImgView, callImgView, iv_arrow_icon;
    MButton btn_type2;
    MTextView ordertitleTxt, storeNameTxt, storeAddressTxt;
    Dialog dialog;
    LinearLayout billDetail_ll;
    LinearLayout footerLayout;
    boolean isShow = true;
    Animation slideUpAnimation, slideDownAnimation, slideup, slidedown;
    /*Pagination*/
    boolean mIsLoading = false;
    boolean isNextPageAvailable = false;
    String next_page_str = "";
    ArrayList<orderItemDetailDataModel> subItemList = new ArrayList<>();
    String isFrom = "";
    Dialog uploadServicePicAlertBox = null;
    Dialog showDeliveryPreferenceAlertBox = null;
    String vImage;

    private RecyclerView orderItemListRecyclerView;
    private RelativeLayout rlOrderItem;
    private LinearLayout trackUserLocationArea, callUserArea;
    private MTextView noItemsTxt;
    private ProgressBar loading_order_item_list;
    private ErrorView errorView;
    // if more than 1 state required
    private ArrayList<orderDetailDataModel> list = new ArrayList<>();
    private OrderItemListRecycleAdapter orderItemListRecycleAdapter;
    private String iOrderId;
    private boolean isDeliver = false;
    private String isPhotoUploaded = "";
    private String PickedFromRes = "";
    private String eBuyAnyService = "";
    private String eAutoaccept = "";
    private String vInstruction = "";
    private String GenieOrderType = "";
    private String selectedImagePath = "";
    private HashMap<String, String> data_trip;
    private String tripId;
    private Location userLocation;

    boolean isPrefrence = false;
    ArrayList<HashMap<String, String>> instructionslit;
    private String vTitle;

    ScrollView main_layout;
    LinearLayout bottomLayout;
    LinearLayout bottomGenieLayout;
    MTextView genieTitletxt;
    MButton btn_type_refresh;
    boolean isImgUploaded = false;
    ProgressBar mProgressBar;
    String ePaymentOption;
    Toolbar toolbar;
    String msg = "", titleDailog = "";
    AlertDialog uploadImgAlertDialog, dialog_declineOrder;
    ImageView clearImg;
    LinearLayout uploadImgArea;
    MaterialEditText priceBox;
    LinearLayout uploadArea;
    Boolean isitemSwitch = true;
    int selpos = 0;
    boolean clickable = true;
    public boolean isGenie = false;
    boolean isFirst = false;
    Menu menu;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_live_track_order_detail);
        this.data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        iOrderId = data_trip.get("iOrderId");
        tripId = data_trip.get("iTripId");
        if (getIntent().hasExtra("isDeliver") && getIntent().getStringExtra("isDeliver").equalsIgnoreCase("Yes")) {
            isDeliver = true;
        } else if (getIntent().hasExtra("isPhotoUploaded")) {
            isPhotoUploaded = getIntent().getStringExtra("isPhotoUploaded");
        }
        if (getIntent().hasExtra("PickedFromRes")) {
            PickedFromRes = getIntent().getStringExtra("PickedFromRes");
        }
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);
        initView();

        toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        bottomLayout = findViewById(R.id.bottomLayout);
        main_layout = findViewById(R.id.main_layout);
        billDetail_ll = findViewById(R.id.billDetail_ll);
        footerLayout = findViewById(R.id.footerLayout);
        iv_arrow_icon = findViewById(R.id.iv_arrow_icon);
        callImgView = findViewById(R.id.callImgView);

        callImgView.setVisibility(View.GONE);


        btn_type2.setId(Utils.generateViewId());
        btn_type2.setAllCaps(false);
        addToClickHandler(btn_type2);
        addToClickHandler(backImgView);
        addToClickHandler(trackUserLocationArea);
        addToClickHandler(callUserArea);
        /*Set actions on view tap*/

        addToClickHandler(callImgView);

        slideUpAnimation = AnimationUtils.loadAnimation(getApplicationContext(),
                R.anim.slide_up_animation);
        slideDownAnimation = AnimationUtils.loadAnimation(getApplicationContext(),
                R.anim.slide_down_animation);

        slideup = AnimationUtils.loadAnimation(getApplicationContext(),
                R.anim.slideup);
        slidedown = AnimationUtils.loadAnimation(getApplicationContext(),
                R.anim.slidedown);

        bottomGenieLayout = findViewById(R.id.bottomGenieLayout);
        mProgressBar = findViewById(R.id.mProgressBar);
        mProgressBar.setIndeterminate(true);
        genieTitletxt = findViewById(R.id.genieTitletxt);
        btn_type_refresh = ((MaterialRippleLayout) findViewById(R.id.btn_type_refresh)).getChildView();
        addToClickHandler(btn_type_refresh);

        setLabels();

        if (savedInstanceState != null) {
            // Restore value of members from saved state
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");

            if (restratValue_str != null && !restratValue_str.equals("") && restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
            }
        }


        if (isPhotoUploaded.equalsIgnoreCase("NO") && !isDeliver && PickedFromRes.equalsIgnoreCase("Yes") && !isGenie) {
            takeAndUploadPic(getActContext());
        } else {
            getOrderDetails();
        }


    }


    public void showDeclineReasonsAlert() {
        titleDailog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_ORDER");
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog, null);
        builder.setView(dialogView);
        MaterialEditText reasonBox = dialogView.findViewById(R.id.inputBox);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            reasonBox.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, 0, 0);
        }
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));
        MTextView cancelTxt = dialogView.findViewById(R.id.cancelTxt);
        MTextView submitTxt = dialogView.findViewById(R.id.submitTxt);
        MTextView subTitleTxt = dialogView.findViewById(R.id.subTitleTxt);
        ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);
        MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
        subTitleTxt.setText(titleDailog);
        submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));
        submitTxt.setOnClickListener(v -> {
            if (!Utils.checkText(reasonBox)) {
                errorTextView.setVisibility(View.VISIBLE);
                errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_CANCEL_REASON"));
                return;
            }
            cancelOrder(Utils.getText(reasonBox));
            dialog_declineOrder.dismiss();

        });
        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            errorTextView.setVisibility(View.GONE);
            dialog_declineOrder.dismiss();
        });

        cancelImg.setOnClickListener(v -> {
            errorTextView.setVisibility(View.GONE);
            Utils.hideKeyboard(getActContext());
            dialog_declineOrder.dismiss();
        });


        dialog_declineOrder = builder.create();
        dialog_declineOrder.setCancelable(false);
        dialog_declineOrder.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        dialog_declineOrder.show();


    }


    private void cancelOrder(String reason) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "cancelDriverOrder");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iOrderId", iOrderId);
        parameters.put("iTripId", tripId);
        parameters.put("vCancelReason", reason);
        parameters.put("eSystem", Utils.eSystem_Type);
        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    if (responseString != null && !responseString.equals("")) {
                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                        if (isDataAvail) {
                            generalFunc.saveGoOnlineInfo();
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), "", generalFunc.retrieveLangLBl("", "LBL_OK"), buttonId -> generalFunc.restartApp());
                        } else {
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });
    }

    public void pubnubmsg(String msg) {
        if (this.msg.equalsIgnoreCase(msg)) {
            return;
        }
        this.msg = msg;
        bottomLayout.setVisibility(View.GONE);
        footerLayout.setVisibility(View.GONE);
        generalFunc.showGeneralMessage("", msg, "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), button_Id -> {
            loading_order_item_list.setVisibility(View.VISIBLE);
            getOrderDetailList(false);
        });

    }

    @Override
    protected void onDestroy() {
        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        super.onDestroy();

    }

    private void initView() {
        instructionslit = new ArrayList<>();
        titleTxt = findViewById(R.id.titleTxt);
        orderIdHTxt = findViewById(R.id.orderIdHTxt);
        noSItemsTxt = findViewById(R.id.noSItemsTxt);
        orderIdVTxt = findViewById(R.id.orderIdVTxt);
        orderDateTxt = findViewById(R.id.orderDateTxt);
        orderTimeVTxt = findViewById(R.id.orderTimeVTxt);
        orderStatusTxt = findViewById(R.id.orderStatusTxt);
        orderTotalBillHTxt = findViewById(R.id.orderTotalBillHTxt);
        orderTotalBillVTxt = findViewById(R.id.orderTotalBillVTxt);
        collectAmountRestHTxt = findViewById(R.id.collectAmountRestHTxt);
        collectAmountRestVTxt = findViewById(R.id.collectAmountRestVTxt);
        collectAmountUserHTxt = findViewById(R.id.collectAmountUserHTxt);
        collectAmountUserVTxt = findViewById(R.id.collectAmountUserVTxt);
        backImgView = findViewById(R.id.backImgView);
        ordertitleTxt = findViewById(R.id.orderinfoTxt);
        storeNameTxt = findViewById(R.id.storeNameTxt);
        storeAddressTxt = findViewById(R.id.storeAddressTxt);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        LinearLayout orderDeliverArea = findViewById(R.id.orderDeliverArea);
        trackUserLocationArea = findViewById(R.id.trackUserLocationArea);
        callUserArea = findViewById(R.id.callUserArea);
        LinearLayout call_navigate_Area = findViewById(R.id.call_navigate_Area);
        restaurantLocationVTxt = findViewById(R.id.restaurantLocationVTxt);
        userNameVTxt = findViewById(R.id.userNameVTxt);
        userAddressTxt = findViewById(R.id.userAddressTxt);
        distanceVTxt = findViewById(R.id.distanceVTxt);
        distanceHTxt = findViewById(R.id.distanceHTxt);
        orderItemListRecyclerView = findViewById(R.id.orderItemListRecyclerView);
        rlOrderItem = findViewById(R.id.rlOrderItem);
        noItemsTxt = findViewById(R.id.noItemsTxt);
        loading_order_item_list = findViewById(R.id.loading_order_item_list);
        errorView = findViewById(R.id.errorView);
        // Set Deliver Area
        if (isDeliver) {
            call_navigate_Area.setVisibility(View.VISIBLE);
            orderItemListRecyclerView.setVisibility(View.GONE);
            rlOrderItem.setVisibility(View.GONE);
            orderDeliverArea.setVisibility(View.VISIBLE);
        } else {
            call_navigate_Area.setVisibility(View.GONE);
            orderItemListRecyclerView.setVisibility(View.VISIBLE);
            rlOrderItem.setVisibility(View.VISIBLE);
            orderDeliverArea.setVisibility(View.GONE);
        }
    }

    @SuppressLint("SetTextI18n")
    public void setLabels() {
        setLableAsPerState();
        genieTitletxt.setText(generalFunc.retrieveLangLBl("", "LBL_GENIE_USER_APPROVAL"));
        btn_type_refresh.setText(generalFunc.retrieveLangLBl("", "LBL_REFRESH"));
        orderIdHTxt.setText(generalFunc.retrieveLangLBl("Order Id", "LBL_ORDER") + " #");
        noSItemsTxt.setText(generalFunc.retrieveLangLBl("Item(s)", "LBL_ITEM_DETAIL_TXT"));
        orderStatusTxt.setText(generalFunc.retrieveLangLBl("Order is Placed", "LBL_ORDER_PLACED_TXT"));
        orderTotalBillHTxt.setText(generalFunc.retrieveLangLBl("Total Bill", "LBL_TOTAL_BILL_TXT"));
        collectAmountRestHTxt.setText(generalFunc.retrieveLangLBl("Pay", "LBL_BTN_PAYMENT_TXT"));
        distanceHTxt.setText(generalFunc.retrieveLangLBl("Distance from Store", "LBL_DISTANCE_FROM_STORE"));
    }


    public void setLableAsPerState() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Delivery", "LBL_DELIVERY_TXT"));
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }


        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));
        ordertitleTxt.setText(isDeliver ? generalFunc.retrieveLangLBl("Order Delivered", "LBL_ORDER_DELIVERED") : generalFunc.retrieveLangLBl("Order PickedUp", "LBL_ORDER_PICKDUP"));
    }


    private void getOrderDetails() {
        subItemList = new ArrayList<>();
        if (!isDeliver) {
            //set true for genie
            orderItemListRecycleAdapter = new OrderItemListRecycleAdapter(getActContext(), subItemList, generalFunc, false, isPhotoUploaded, false);
            orderItemListRecyclerView.setAdapter(orderItemListRecycleAdapter);
            orderItemListRecycleAdapter.setSubItemList(subItemList, isPhotoUploaded);
            orderItemListRecycleAdapter.notifyDataSetChanged();
            orderItemListRecycleAdapter.setOnItemClickListener(this);

            orderItemListRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
                @Override
                public void onScrollStateChanged(@NotNull RecyclerView recyclerView, int newState) {
                    super.onScrollStateChanged(recyclerView, newState);
                }

                @Override
                public void onScrolled(@NotNull RecyclerView recyclerView, int dx, int dy) {
                    super.onScrolled(recyclerView, dx, dy);

                    int visibleItemCount = recyclerView.getLayoutManager().getChildCount();
                    int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {

                        mIsLoading = true;
                        orderItemListRecycleAdapter.addFooterView();

                        getOrderDetailList(true);

                    } else if (!isNextPageAvailable) {
                        orderItemListRecycleAdapter.removeFooterView();
                    }
                }
            });

        }
        getOrderDetailList(false);
    }

    @Override
    public void onItemClickList(int position, String pickedFromRes) {
    }

    @SuppressLint("SetTextI18n")
    private void showPreferenceHelp(String name, String qty, int pos) {
        if (uploadImgAlertDialog != null && uploadImgAlertDialog.isShowing()) {
            return;
        }

        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.desgin_genie_upload_image, null);
        builder.setView(dialogView);


        final ImageView iamage_source = dialogView.findViewById(R.id.iamage_source);
        iamage_source.setImageResource(R.drawable.ic_pencil_genie);

        final ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);
        final MTextView chooseFileTitleTxt = dialogView.findViewById(R.id.chooseFileTitleTxt);
        final MTextView payNotTxt = dialogView.findViewById(R.id.payNotTxt);
        final ImageView payinfo = dialogView.findViewById(R.id.payinfo);
        final LinearLayout priceBoxArea = dialogView.findViewById(R.id.priceBoxArea);
        final MTextView itemAvailTxt = dialogView.findViewById(R.id.itemAvailTxt);
        final ImageView iteminfo = dialogView.findViewById(R.id.iteminfo);
        SwitchButton itemSwitch = dialogView.findViewById(R.id.itemSwitch);
        iteminfo.setOnClickListener(view -> {
            if (GenieOrderType != null && GenieOrderType.equalsIgnoreCase("Runner")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NOTE_ITEM_UNAVAILABLE_RUNNER"));
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NOTE_ITEM_UNAVAILABLE_GENIE"));
            }

        });

        final MTextView titileTxt = dialogView.findViewById(R.id.titileTxt);
        View mCardView = dialogView.findViewById(R.id.mCardView);


        final MTextView uploadTitleTxt = dialogView.findViewById(R.id.uploadTitleTxt);
        uploadImgArea = dialogView.findViewById(R.id.uploadImgArea);
        uploadArea = dialogView.findViewById(R.id.uploadArea);
        clearImg = dialogView.findViewById(R.id.clearImg);
        clearImg.setVisibility(View.GONE);
        priceBox = dialogView.findViewById(R.id.priceBox);
        payNotTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PAYMENT_NOT_REQUIRED"));
        payinfo.setOnClickListener(view -> generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PAYMENT_NOT_REQUIRED_HELP_INFO")));

        if (subItemList.get(pos).getExtrapayment() != null && subItemList.get(pos).getExtrapayment().equalsIgnoreCase("No")) {
            payNotTxt.setVisibility(View.VISIBLE);
            payinfo.setVisibility(View.VISIBLE);
            priceBoxArea.setVisibility(View.GONE);

        }


        itemSwitch.setOnCheckedChangeListener((compoundButton, b) -> {
            isitemSwitch = b;
            if (b) {
                itemSwitch.setBackColorRes(R.color.Green);
                uploadArea.setVisibility(View.VISIBLE);
                if (subItemList.get(pos).getExtrapayment() != null && subItemList.get(pos).getExtrapayment().equalsIgnoreCase("No")) {
                    priceBoxArea.setVisibility(View.GONE);
                } else {
                    priceBoxArea.setVisibility(View.VISIBLE);
                }
            } else {
                itemSwitch.setBackColorRes(android.R.color.holo_red_dark);
                uploadArea.setVisibility(View.GONE);
                priceBoxArea.setVisibility(View.GONE);
            }


        });
        if (subItemList.get(pos).geteItemAvailable() != null && subItemList.get(pos).geteItemAvailable().equalsIgnoreCase("Yes")) {
            isitemSwitch = true;
            itemSwitch.setChecked(true);
            itemSwitch.setBackColorRes(R.color.Green);
            uploadArea.setVisibility(View.VISIBLE);
            if (subItemList.get(pos).getExtrapayment() != null && subItemList.get(pos).getExtrapayment().equalsIgnoreCase("No")) {
                priceBoxArea.setVisibility(View.GONE);
            } else {
                priceBoxArea.setVisibility(View.VISIBLE);
            }

        } else {
            isitemSwitch = false;
            Logger.d("itemSwitch", "::" + false);
            itemSwitch.setChecked(false);
            itemSwitch.setBackColorRes(android.R.color.holo_red_dark);
            uploadArea.setVisibility(View.GONE);
            priceBoxArea.setVisibility(View.GONE);

        }


        priceBox.setInputType(InputType.TYPE_NUMBER_FLAG_DECIMAL | InputType.TYPE_CLASS_NUMBER);
        priceBox.setHideUnderline(true);
        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setAllCaps(false);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));
        btn_type2.setId(Utils.generateViewId());
        //priceBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_ITEM_AMOUNT"));
        priceBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_ENTER_ITEM_AMOUNT"));
        titileTxt.setText(name + " x " + qty);
        itemAvailTxt.setText(generalFunc.retrieveLangLBl("Item Available ?", "LBL_ITEM_AVAILABLE"));
        chooseFileTitleTxt.setText(generalFunc.retrieveLangLBl("Choose file", "LBL_CHOOSE_FILE"));
        if (subItemList.get(pos).getItemPrice() != null && GeneralFunctions.parseDoubleValue(0, subItemList.get(pos).getfTotPriceWithoutSymbol()) > 0) {
            priceBox.setText(subItemList.get(pos).getfTotPriceWithoutSymbol());
        }
        if (subItemList.get(pos).getvImageUploaded() != null && subItemList.get(pos).getvImageUploaded().equalsIgnoreCase("Yes")) {
            isImgUploaded = true;
            selectedImagePath = subItemList.get(pos).getItemDetailsUpdated();
            clearImg.setVisibility(View.VISIBLE);
            uploadImgArea.setClickable(false);
            dialogView.findViewById(R.id.camImgVIew).setVisibility(View.GONE);
            dialogView.findViewById(R.id.chooseFileTitleTxt).setVisibility(View.GONE);
            BitmapFactory.Options options = new BitmapFactory.Options();
            options.inJustDecodeBounds = true;
            BitmapFactory.decodeFile(subItemList.get(pos).getvImage(), options);
            new LoadImage.builder(LoadImage.bind(subItemList.get(pos).getvImage()), (dialogView.findViewById(R.id.uploadImgVIew))).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();


        }


        uploadTitleTxt.setText(generalFunc.retrieveLangLBl("Upload Item image", "LBL_UPLOAD_ITEM_PHOTO"));

        cancelImg.setOnClickListener(v -> {
            uploadImgAlertDialog.dismiss();
            uploadImgAlertDialog = null;

        });

        uploadImgArea.setOnClickListener(view -> {
            isGenie = true;
            getFileSelector().openFileSelection(FileSelector.FileType.Image);
        });

        btn_type2.setOnClickListener(view -> {
            if (uploadArea.getVisibility() == View.VISIBLE) {
                boolean isPriceEnter = Utils.checkText(priceBox);
                boolean isImageSelect = Utils.checkText(selectedImagePath);
                if (!isPriceEnter && priceBoxArea.getVisibility() == View.VISIBLE) {
                    priceBox.setError(generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));
                }
                if ((isPriceEnter && priceBoxArea.getVisibility() == View.VISIBLE) && GeneralFunctions.parseDoubleValue(0, Utils.getText(priceBox)) == 0) {
                    priceBox.setError(generalFunc.retrieveLangLBl("", "LBL_ITEM_PRICE_GREATER_THAN_ZERO_TXT"));
                    return;
                }
                if (!isImageSelect) {
                    mCardView.setBackgroundResource(R.drawable.error_border);

                }

                if ((!isPriceEnter && priceBoxArea.getVisibility() == View.VISIBLE) || (!isImageSelect && !isImgUploaded)) {
                    return;
                }
            }
            checkItemUploadData();
        });
        uploadImgAlertDialog = builder.create();
        uploadImgAlertDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(uploadImgAlertDialog);
        uploadImgAlertDialog.getWindow().setBackgroundDrawable(new ColorDrawable(android.graphics.Color.TRANSPARENT));
        uploadImgAlertDialog.show();


    }

    @Override
    public void onItemImageUpload(int position) {
        selpos = position;
        isImgUploaded = false;
        selectedImagePath = "";
        showPreferenceHelp(subItemList.get(position).getItemName(), subItemList.get(position).getItemQuantity(), position);

    }

    public void onLocationUpdate(Location location) {
        this.userLocation = location;
    }


    private void GenieBuildOrderStatusConfirmation() {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else if (btn_id == 1) {
                orderPickedUpOrDeliver(list.get(0).getTotalAmount(), false);
            }
        });
        generateAlert.setContentMessage("", !isDeliver ? generalFunc.retrieveLangLBl("", "LBL_GENIE_ITEM_EDITED") : generalFunc.retrieveLangLBl("Kindly Confirm to mark order as delivered ?", "LBL_ORDER_DELIVERED_CONFIRMATION_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        generateAlert.showAlertBox();
    }

    private void BuildOrderStatusConfirmation(boolean redirectToPhotoUpload) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else if (btn_id == 1) {
                orderPickedUpOrDeliver(list.get(0).getTotalAmount(), redirectToPhotoUpload);
            }
        });
        generateAlert.setContentMessage("", !isDeliver ? generalFunc.retrieveLangLBl("", "LBL_ORDER_PICKEDUP_CONFIRMATION") : generalFunc.retrieveLangLBl("Kindly Confirm to mark order as delivered ?", "LBL_ORDER_DELIVERED_CONFIRMATION_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        generateAlert.showAlertBox();
    }

    public void getOrderDetailList(final boolean isLoadMore) {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetOrderDetailsRestaurant");
        parameters.put("iOrderId", iOrderId);
        parameters.put("UserType", Utils.app_type);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }
        parameters.put("eSystem", Utils.eSystem_Type);

        noItemsTxt.setVisibility(View.GONE);
        list.clear();
        subItemList.clear();

        if (orderItemListRecycleAdapter != null) {
            orderItemListRecycleAdapter.notifyDataSetChanged();

        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            noItemsTxt.setVisibility(View.GONE);
            closeLoader();
            if (responseStringObject != null) {


                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                    list = new ArrayList<>();
                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObject);

                    JSONObject msg_obj = generalFunc.getJsonObject("message", responseStringObject);
                    JSONArray itemList = generalFunc.getJsonArray("itemlist", msg_obj.toString());

                    // Order's Details Add
                    orderDetailDataModel orderDetail = new orderDetailDataModel();
                    orderDetail.setOrderID(generalFunc.getJsonValueStr("iOrderId", msg_obj));
                    orderDetail.setvOrderNo(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vOrderNo", msg_obj)));
                    orderDetail.setIsPhotoUploaded(generalFunc.getJsonValueStr("isPhotoUploaded", msg_obj));
                    orderDetail.setvVehicleType(generalFunc.getJsonValueStr("vVehicleType", msg_obj));

                    /*orderDetail.setOrderDate_Time(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("tOrderRequestDate", msg_obj)));
                    String tOrderRequestDate_Org = generalFunc.getJsonValueStr("tOrderRequestDate_Org", msg_obj);
                    String formattedDate = generalFunc.getDateFormatedType(tOrderRequestDate_Org, Utils.OriginalDateFormate, DateTimeUtils.getDetailDateFormat(generalFunc));
                    orderDetail.setOrderDate_Time(generalFunc.convertNumberWithRTL(formattedDate));*/

                    orderDetail.setDisplayDate(generalFunc.getJsonValueStr("tDisplayDate", msg_obj));
                    orderDetail.setDisplayTime(generalFunc.getJsonValueStr("tDisplayTimeAbbr", msg_obj));
                    orderDetail.setDisplayDateTime(generalFunc.getJsonValueStr("tDisplayDateTime", msg_obj));

                    orderDetail.setTotalAmount(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("originalTotal", msg_obj)));
                    orderDetail.setCurrencySymbol(generalFunc.getJsonValueStr("vSymbol", msg_obj));
                    orderDetail.setTotalAmountWithSymbol(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("SubTotal", msg_obj)));
                    orderDetail.setTotalItems(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TotalItems", msg_obj)));
                    orderDetail.setUserPhone(generalFunc.getJsonValueStr("UserPhone", msg_obj));
                    orderDetail.setUserName(generalFunc.getJsonValueStr("UserName", msg_obj));
                    orderDetail.setUserDistance(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("UserDistance", msg_obj)));
                    String userAddress = generalFunc.getJsonValueStr("UserAddress", msg_obj);
                    orderDetail.setUserAddress(Utils.checkText(userAddress) ? StringUtils.capitalize(userAddress) : userAddress);
                    orderDetail.setUserLatitude(generalFunc.getJsonValueStr("UserLatitude", msg_obj));
                    orderDetail.setUserLongitude(generalFunc.getJsonValueStr("UserLongitude", msg_obj));

                    orderDetail.setePaid(generalFunc.getJsonValueStr("ePaid", msg_obj));

                    orderDetail.setePaymentOption(generalFunc.getJsonValueStr("ePaymentOption", msg_obj));

                    ePaymentOption = generalFunc.getJsonValueStr("ePaymentOption", msg_obj);


                    String restAddress = generalFunc.getJsonValueStr("vRestuarantLocation", msg_obj);
                    orderDetail.setRestaurantAddress(Utils.checkText(restAddress) ? StringUtils.capitalize(restAddress) : restAddress);

                    orderDetail.setRestaurantName(generalFunc.getJsonValueStr("vCompany", msg_obj));
                    orderDetail.setRestaurantId(generalFunc.getJsonValueStr("iCompanyId", msg_obj));
                    orderDetail.setRestaurantImage(generalFunc.getJsonValueStr("vRestuarantImage", msg_obj));
                    orderDetail.setRestaurantLattitude(generalFunc.getJsonValueStr("RestuarantLat", msg_obj));
                    orderDetail.setRestaurantLongitude(generalFunc.getJsonValueStr("RestuarantLong", msg_obj));
                    orderDetail.setRestaurantNumber(generalFunc.getJsonValueStr("RestuarantPhone", msg_obj));


                    JSONObject DeliveryPreferences = generalFunc.getJsonObject("DeliveryPreferences", responseStringObject);

                    isPrefrence = generalFunc.getJsonValueStr("Enable", DeliveryPreferences).equalsIgnoreCase("Yes");
                    if (isPrefrence) {

                        btn_type2.setText(generalFunc.retrieveLangLBl("Next", "LBL_NEXT"));
                        vTitle = generalFunc.getJsonValueStr("vTitle", DeliveryPreferences);
                        JSONArray Data = generalFunc.getJsonArray("Data", DeliveryPreferences);
                        if (Data != null) {
                            for (int i = 0; i < Data.length(); i++) {
                                try {
                                    JSONObject jsonObject = (JSONObject) Data.get(i);
                                    String tTitle = generalFunc.getJsonValueStr("tTitle", jsonObject);
                                    String tDescription = generalFunc.getJsonValueStr("tDescription", jsonObject);
                                    String ePreferenceFor = generalFunc.getJsonValueStr("ePreferenceFor", jsonObject);
                                    String eImageUpload = generalFunc.getJsonValueStr("eImageUpload", jsonObject);
                                    String iDisplayOrder = generalFunc.getJsonValueStr("iDisplayOrder", jsonObject);
                                    String eContactLess = generalFunc.getJsonValueStr("eContactLess", jsonObject);
                                    String eStatus = generalFunc.getJsonValueStr("eStatus", jsonObject);
                                    String iPreferenceId = generalFunc.getJsonValueStr("iPreferenceId", jsonObject);
                                    HashMap<String, String> hashMap = new HashMap<>();

                                    hashMap.put("tTitle", tTitle);
                                    hashMap.put("tDescription", tDescription);
                                    hashMap.put("ePreferenceFor", ePreferenceFor);
                                    hashMap.put("eImageUpload", eImageUpload);
                                    hashMap.put("iDisplayOrder", iDisplayOrder);
                                    hashMap.put("eContactLess", eContactLess);
                                    hashMap.put("eStatus", eStatus);
                                    hashMap.put("iPreferenceId", iPreferenceId);
                                    instructionslit.add(hashMap);

                                } catch (JSONException e) {
                                    Logger.e("Exception", "::" + e.getMessage());
                                }
                            }

                        }
                    }
                    eBuyAnyService = generalFunc.getJsonValueStr("eBuyAnyService", msg_obj);
                    eAutoaccept = generalFunc.getJsonValueStr("eAutoaccept", msg_obj);
                    vInstruction = generalFunc.getJsonValueStr("vInstruction", msg_obj);
                    GenieOrderType = generalFunc.getJsonValueStr("GenieOrderType", msg_obj);

                    if (eBuyAnyService.equalsIgnoreCase("Yes")) {
                        onCreateOptionsMenu(menu);
                    }
                    collectAmountUserHTxt.setText(generalFunc.getJsonValueStr("InvoiceTitle", msg_obj));

                    if (itemList != null && itemList.length() > 0) {
                        ArrayList<orderItemDetailDataModel> subItemList = new ArrayList<>();

                        for (int i = 0; i < itemList.length(); i++) {
                            orderItemDetailDataModel orderItemList = new orderItemDetailDataModel();

                            JSONObject item_list_detail = generalFunc.getJsonObject(itemList, i);
                            if (eBuyAnyService.equalsIgnoreCase("Yes")) {
                                orderItemList.setIsGenie("Yes");
                                orderItemList.setvImageUploaded(generalFunc.getJsonValueStr("vImageUploaded", item_list_detail));
                                orderItemList.seteDecline(generalFunc.getJsonValueStr("eDecline", item_list_detail));
                                orderItemList.setItemDetailsUpdated(generalFunc.getJsonValueStr("itemDetailsUpdated", item_list_detail));
                            } else {
                                orderItemList.setIsGenie("No");
                            }

                            orderItemList.seteItemAvailable(generalFunc.getJsonValueStr("eItemAvailable", item_list_detail));
                            orderItemList.setExtrapayment(generalFunc.getJsonValueStr("eExtraPayment", item_list_detail));
                            orderItemList.setItemName(generalFunc.getJsonValueStr("MenuItem", item_list_detail));
                            orderItemList.setItemQuantity(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("iQty", item_list_detail)));
                            orderItemList.setItemPrice(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fTotPrice", item_list_detail)));
                            orderItemList.setSubItemName(generalFunc.getJsonValueStr("SubTitle", item_list_detail));
                            orderItemList.seteAvailable(generalFunc.getJsonValueStr("eAvailable", item_list_detail));
                            orderItemList.setvImage(generalFunc.getJsonValueStr("vImage", item_list_detail));
                            orderItemList.setiOrderDetailId(generalFunc.getJsonValueStr("iOrderDetailId", item_list_detail));
                            orderItemList.setItemTotalPrice(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fTotPrice", item_list_detail)));
                            orderItemList.setfTotPriceWithoutSymbol(generalFunc.getJsonValueStr("fTotPriceWithoutSymbol", item_list_detail));
                            orderItemList.setTotalDiscountPrice(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TotalDiscountPrice", item_list_detail)));
                            subItemList.add(orderItemList);
                        }
                        orderDetail.setorderItemDetailList(subItemList);
                    }

                    list.add(orderDetail);
                    if (isFirst) {

                        if (!vInstruction.equalsIgnoreCase("") && eAutoaccept.equalsIgnoreCase("Yes")) {
                            PreferenceDailogJava preferenceDailogJava = new PreferenceDailogJava(getActContext());
                            preferenceDailogJava.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_VIEW_SPEC_INS_FOR_STORE"), vInstruction, R.drawable.ic_notes, false,
                                    generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), "", false);

                            menu.findItem(R.id.intruction_store).setVisible(true);

                        }
                    }

                    storeNameTxt.setText(orderDetail.getRestaurantName());
                    storeAddressTxt.setText(restAddress);

                    if (!orderDetail.getRestaurantImage().equals("")) {
                        vImage = CommonUtilities.COMPANY_PHOTO_PATH + orderDetail.getRestaurantId() + "/"
                                + orderDetail.getRestaurantImage();
                    } else {
                        vImage = "temp";
                    }
                    new LoadImage.builder(LoadImage.bind(vImage), findViewById(R.id.storeImageView)).setPlaceholderImagePath(R.mipmap.ic_no_icon).setErrorImagePath(R.mipmap.ic_no_icon).build();


                    setOrderDetails();

                    if (orderItemListRecycleAdapter != null) {
                        orderItemListRecycleAdapter.notifyDataSetChanged();
                    }

                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }

                    if (eBuyAnyService.equalsIgnoreCase("Yes")) {
                        if (generalFunc.getJsonValueStr("genieWaitingForUserApproval", msg_obj).equalsIgnoreCase("Yes") &&
                                generalFunc.getJsonValueStr("genieUserApproved", msg_obj).equalsIgnoreCase("No")) {
                            manageGenieRefreshView();
                            orderItemListRecycleAdapter.setEditable(false);

                        }
                        if (!generalFunc.getJsonValueStr("ePaid", msg_obj).equalsIgnoreCase("Yes")) {
                            if (generalFunc.getJsonValueStr("genieWaitingForUserApproval", msg_obj).equalsIgnoreCase("Yes") &&
                                    generalFunc.getJsonValueStr("genieUserApproved", msg_obj).equalsIgnoreCase("Yes")) {
                                orderItemListRecycleAdapter.setEditable(false);
                                if (ePaymentOption.equalsIgnoreCase("Cash")) {
                                    bottomGenieLayout.setVisibility(View.GONE);
                                    bottomLayout.setVisibility(View.VISIBLE);
                                    footerLayout.setVisibility(View.VISIBLE);
                                    RelativeLayout.LayoutParams params = new RelativeLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT);
                                    params.addRule(RelativeLayout.ABOVE, R.id.footerLayout);
                                    params.addRule(RelativeLayout.BELOW, R.id.toolbar_include);
                                    main_layout.setLayoutParams(params);

                                } else {
                                    manageGenieRefreshView();
                                }
                                genieTitletxt.setText(generalFunc.retrieveLangLBl("Waiting for user to make payment", "LBL_GENIE_PAYMENT_WAITING"));
                            }
                        }
                        if (generalFunc.getJsonValueStr("ePaid", msg_obj).equalsIgnoreCase("Yes")) {
                            orderItemListRecycleAdapter.setEditable(false);
                            bottomGenieLayout.setVisibility(View.GONE);
                            bottomLayout.setVisibility(View.VISIBLE);
                            footerLayout.setVisibility(View.VISIBLE);

                            RelativeLayout.LayoutParams params = new RelativeLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT);
                            params.addRule(RelativeLayout.ABOVE, R.id.footerLayout);
                            params.addRule(RelativeLayout.BELOW, R.id.toolbar_include);
                            main_layout.setLayoutParams(params);
                            orderItemListRecycleAdapter.setEditable(false);

                        }

                    }


                } else {
                    if (list.size() == 0) {
                        removeNextPageConfig();
                        noItemsTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        noItemsTxt.setVisibility(View.VISIBLE);
                    }
                }

                if (orderItemListRecycleAdapter != null) {
                    orderItemListRecycleAdapter.notifyDataSetChanged();

                }


            } else {
                if (!isLoadMore) {
                    removeNextPageConfig();
                    generateErrorView();
                }

            }

            mIsLoading = false;
        });


    }

    public void checkItemUploadData() {


        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "UpdateOrderReviewItemDetails");
            put("MemberType", Utils.app_type);
            put("iOrderDetailId", subItemList.get(selpos).getiOrderDetailId());
            put("iOrderId", iOrderId);
            put("iItemPrice", Utils.getText(priceBox));
            put("vImage", selectedImagePath);
            put("eItemAvailable", isitemSwitch ? "Yes" : "No");
            put("eSystem", Utils.eSystem_Type);
        }};
        UploadProfileImage uploadProfileImage = new UploadProfileImage(LiveTrackOrderDetailActivity.this, isImgUploaded ? "" : selectedImagePath, Utils.TempProfileImageName, paramsList, "");


        uploadProfileImage.execute(!isImgUploaded && priceBox.getVisibility() != View.GONE, generalFunc.retrieveLangLBl("", "LBL_GENIE_IMAGE_UPLOADING"));

    }

    @SuppressLint("SetTextI18n")
    private void setOrderDetails() {
        if (list.size() > 0) {

            orderDetailDataModel orderDetailDataModel = list.get(0);

            subItemList.clear();
            subItemList.addAll(orderDetailDataModel.getorderItemDetailList());
            if (orderItemListRecycleAdapter != null) {
                orderItemListRecycleAdapter.setSubItemList(subItemList, isPhotoUploaded);
            }
            collectAmountRestHTxt.setText(generalFunc.retrieveLangLBl("Pay", "LBL_BTN_PAYMENT_TXT") + " " + orderDetailDataModel.getRestaurantName());
            orderIdVTxt.setText("#" + orderDetailDataModel.getvOrderNo());
            orderTotalBillVTxt.setText(" " + orderDetailDataModel.getTotalAmountWithSymbol());
            collectAmountRestVTxt.setText(" " + orderDetailDataModel.getResturantPayAmount());

            if (Utils.checkText(orderDetailDataModel.getUserName())) {
                userNameVTxt.setText(" " + StringUtils.capitalize(orderDetailDataModel.getUserName()));
            }

            if (Utils.checkText(orderDetailDataModel.getUserAddress())) {
                userAddressTxt.setText(" " + orderDetailDataModel.getUserAddress());
            }

            restaurantLocationVTxt.setText(orderDetailDataModel.getRestaurantName() + "\n" + orderDetailDataModel.getRestaurantAddress());
            distanceVTxt.setText(orderDetailDataModel.getUserDistance());

            if (orderDetailDataModel.getePaymentOption().equalsIgnoreCase("Cash")) {
                collectAmountUserVTxt.setText(" " + orderDetailDataModel.getTotalAmountWithSymbol());
            } else {
                collectAmountUserVTxt.setText(Html.fromHtml(" " + orderDetailDataModel.getTotalAmountWithSymbol() + "<br><small><font color='#434343'>"
                        + generalFunc.retrieveLangLBl("(Paid By User)", "LBL_PAYMENT_DONE_BY_USER") + "</font></small>"));
            }

            String atText = generalFunc.retrieveLangLBl("at", "LBL_AT_TXT");
            /*String[] timeDateString = orderDetailDataModel.getOrderDate_Time().split(" " + atText + " ");
            if (timeDateString.length > 0) orderDateTxt.setText(timeDateString[0].trim());
            if (timeDateString.length > 1) orderTimeVTxt.setText(timeDateString[1].trim());*/
            //orderDateTxt.setText(" " + orderDetailDataModel.getOrderDate_Time());
            orderDateTxt.setText(" " + orderDetailDataModel.getDisplayDate());
            orderTimeVTxt.setText(" " + orderDetailDataModel.getDisplayTime());
            noSItemsTxt.setText(orderDetailDataModel.getTotalItems() + " " + generalFunc.retrieveLangLBl("Item(s)", "LBL_ITEM_DETAIL_TXT"));

        }
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }


        errorView.setOnRetryListener(() -> getOrderDetailList(false));
    }

    public void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        if (orderItemListRecycleAdapter != null) {
            orderItemListRecycleAdapter.removeFooterView();
        }
    }

    public void closeLoader() {
        if (loading_order_item_list.getVisibility() == View.VISIBLE) {
            loading_order_item_list.setVisibility(View.GONE);
            main_layout.setVisibility(View.VISIBLE);
            footerLayout.setVisibility(View.VISIBLE);
            bottomLayout.setVisibility(View.VISIBLE);
        }
    }

    public void showBill() {
        if (isShow) {
            footerLayout.startAnimation(slidedown);
            billDetail_ll.startAnimation(slideDownAnimation);

            slidedown.setAnimationListener(new Animation.AnimationListener() {
                @Override
                public void onAnimationStart(Animation animation) {

                }

                @Override
                public void onAnimationEnd(Animation animation) {
                    billDetail_ll.setVisibility(View.GONE);
                    isShow = false;
                    iv_arrow_icon.setImageResource(R.mipmap.ic_arrow_up);
                }

                @Override
                public void onAnimationRepeat(Animation animation) {

                }
            });


        } else {
            isShow = true;
            footerLayout.startAnimation(slideUpAnimation);
            billDetail_ll.setVisibility(View.VISIBLE);
            billDetail_ll.startAnimation(slideUpAnimation);


            slideUpAnimation.setAnimationListener(new Animation.AnimationListener() {
                @Override
                public void onAnimationStart(Animation animation) {

                }

                @Override
                public void onAnimationEnd(Animation animation) {
                    iv_arrow_icon.setImageResource(R.mipmap.ic_arrow_down);
                }

                @Override
                public void onAnimationRepeat(Animation animation) {

                }
            });

        }
    }

    @SuppressLint("SetTextI18n")
    public void showBillDialog() {

        dialog = new Dialog(getActContext(), R.style.My_Dialog);
        dialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        dialog.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        dialog.setContentView(R.layout.design_bill_dialog);

        MTextView submitDetailHTxt = dialog.findViewById(R.id.submitDetailHTxt);
        MTextView billValueHTxt = dialog.findViewById(R.id.billValueHTxt);
        MTextView billValueCTxt = dialog.findViewById(R.id.billValueCTxt);
        MTextView confirmBillHTxt = dialog.findViewById(R.id.confirmBillHTxt);
        MTextView confirmBillCTxt = dialog.findViewById(R.id.confirmBillCTxt);
        MTextView billCollectedHTxt = dialog.findViewById(R.id.billCollectedHTxt);
        MTextView billCollectedCTxt = dialog.findViewById(R.id.billCollectedCTxt);
        MTextView paidBillCTxt = dialog.findViewById(R.id.paidBillCTxt);
        MTextView cancelHTxt = dialog.findViewById(R.id.cancelHTxt);
        MTextView confirmHTxt = dialog.findViewById(R.id.confirmHTxt);

        if (Utils.checkText(list.get(0).getCurrencySymbol())) {
            billValueCTxt.setText("" + list.get(0).getCurrencySymbol());
            confirmBillCTxt.setText("" + list.get(0).getCurrencySymbol());
            billCollectedCTxt.setText("" + list.get(0).getCurrencySymbol());
            paidBillCTxt.setText("" + list.get(0).getCurrencySymbol());
        }

        LinearLayout ll_order_collect_Area = dialog.findViewById(R.id.ll_order_collect_Area);
        LinearLayout ll_order_deliver_Area = dialog.findViewById(R.id.ll_order_deliver_Area);
        final String required_str = generalFunc.retrieveLangLBl("Required", "LBL_FEILD_REQUIRD");

        submitDetailHTxt.setText(generalFunc.retrieveLangLBl("Submit Detail", "LBL_SUBMIT_DETAILS"));
        billValueHTxt.setText(generalFunc.retrieveLangLBl("Bill Value", "LBL_BILL_VALUE_TXT"));
        confirmBillHTxt.setText(generalFunc.retrieveLangLBl("Confirm Bill Value", "LBL_CONFIRM_BILL_VALUE_TXT"));
        billCollectedHTxt.setText(generalFunc.retrieveLangLBl("Collected", "LBL_COLLECTED_TXT"));

        cancelHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        confirmHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));

        MaterialEditText billValueEditText = dialog.findViewById(R.id.billValueEditText);
        MaterialEditText confirmBillValueEditText = dialog.findViewById(R.id.confirmBillValueEditText);
        MaterialEditText paidValueEditText = dialog.findViewById(R.id.paidValueEditText);
        MaterialEditText billCollecetdValueEditText = dialog.findViewById(R.id.billCollecetdValueEditText);

        billValueEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_SIGNED | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        confirmBillValueEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_SIGNED | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        paidValueEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_SIGNED | InputType.TYPE_NUMBER_FLAG_DECIMAL);

        billCollecetdValueEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_SIGNED | InputType.TYPE_NUMBER_FLAG_DECIMAL);

        if (isDeliver) {
            ll_order_collect_Area.setVisibility(View.VISIBLE);
            ll_order_deliver_Area.setVisibility(View.GONE);
        }

        cancelHTxt.setOnClickListener(view -> dialog.dismiss());

        confirmHTxt.setOnClickListener(view -> {

            if (isDeliver) {
                double enteredValue = GeneralFunctions.parseDoubleValue(0.00, Utils.getText(billCollecetdValueEditText));
                boolean isBillAmountCollectedEntered = Utils.checkText(billCollecetdValueEditText) && enteredValue > 0 || Utils.setErrorFields(billCollecetdValueEditText, required_str);

                if (!isBillAmountCollectedEntered) {
                    return;
                }

                orderPickedUpOrDeliver(Utils.getText(billCollecetdValueEditText).trim(), false);
            } else {
                double enteredValue = GeneralFunctions.parseDoubleValue(0.00, Utils.getText(billValueEditText));
                double reEnteredValue = GeneralFunctions.parseDoubleValue(0.00, Utils.getText(confirmBillValueEditText));
                /*Check fist entered amount not blank or Zero */
                boolean isBillAmountEntered = Utils.checkText(billValueEditText) && enteredValue > 0 || Utils.setErrorFields(billValueEditText, required_str);

                if (!isBillAmountEntered) {
                    return;
                }

                /*Check Confirmed Second entered amount not blank or Zero */
                boolean isReBillAmountEnter = Utils.checkText(confirmBillValueEditText) && reEnteredValue > 0 || Utils.setErrorFields(confirmBillValueEditText, required_str);


                if (!isReBillAmountEnter) {
                    return;
                }
                /*Check Confirmed Second entered amount match with first entered amout which is same as final total */
                if (reEnteredValue != enteredValue) {
                    Utils.setErrorFields(confirmBillValueEditText, generalFunc.retrieveLangLBl("Bill value is not same.", "LBL_VERIFY_BILL_VALUE_ERROR_TXT"));
                    return;
                }

                orderPickedUpOrDeliver(Utils.getText(confirmBillValueEditText).trim(), true);

            }

        });

        dialog.setCancelable(false);
        dialog.setCanceledOnTouchOutside(false);
        dialog.show();
    }

    private void takeAndUploadPic(final Context mContext) {
        isFrom = "";
        selectedImagePath = "";

        uploadServicePicAlertBox = new Dialog(mContext, R.style.Theme_Dialog);
        uploadServicePicAlertBox.requestWindowFeature(Window.FEATURE_NO_TITLE);

        uploadServicePicAlertBox.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        uploadServicePicAlertBox.setContentView(R.layout.design_upload_service_pic);
        uploadServicePicAlertBox.setCancelable(false);

        MTextView titleTxt = uploadServicePicAlertBox.findViewById(R.id.titleTxt);
        final MTextView uploadStatusTxt = uploadServicePicAlertBox.findViewById(R.id.uploadStatusTxt);
        MTextView uploadTitleTxt = uploadServicePicAlertBox.findViewById(R.id.uploadTitleTxt);
        ImageView backImgView = uploadServicePicAlertBox.findViewById(R.id.backImgView);
        MTextView skipTxt = uploadServicePicAlertBox.findViewById(R.id.skipTxt);
        final ImageView uploadImgVIew = uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew);
        LinearLayout uploadImgArea = uploadServicePicAlertBox.findViewById(R.id.uploadImgArea);
        MButton btn_type2 = ((MaterialRippleLayout) uploadServicePicAlertBox.findViewById(R.id.btn_type2)).getChildView();

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_IMAGE_SERVICE"));
        skipTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SKIP_TXT"));


        uploadTitleTxt.setText(generalFunc.retrieveLangLBl("Click and upload to submit proof of your arrival to the restaurant.Like restaurant's pic OR it's menu or order bill OR anything which shows you are at restaurant .", "LBL_UPLOAD_ORDER_PICKUP_PROOF_MSG_TXT"));
        btn_type2.setText(generalFunc.retrieveLangLBl("Save Proof And Picked Up Order", "LBL_SAVE_PROOF_ORDER_PICKUP_TXT"));


        btn_type2.setId(Utils.generateViewId());


        uploadImgArea.setOnClickListener(view -> {
            if (clickable) {
                clickable = false;
                getFileSelector().openFileSelection(FileSelector.FileType.Image);
            }
            new Handler(Looper.myLooper()).postDelayed(() -> clickable = true, 1000);
        });
        btn_type2.setOnClickListener(view -> {
            if (!Utils.checkText(selectedImagePath)) {
                uploadStatusTxt.setVisibility(View.VISIBLE);
                generalFunc.showMessage(uploadStatusTxt, "Please select image");
            } else {
                isFrom = "pickupproof";
                uploadStatusTxt.setVisibility(View.GONE);
                OrderImageUpload("No");
            }
        });

        skipTxt.setOnClickListener(view -> {

            isFrom = "";
            selectedImagePath = "";
            uploadImgVIew.setImageURI(null);
            OrderImageUpload("Yes");


        });
        backImgView.setVisibility(View.GONE);
        backImgView.setOnClickListener(view -> closeuploadServicePicAlertBox());
        if (uploadServicePicAlertBox != null && !this.isFinishing()) {
            LayoutDirection.setLayoutDirection(uploadServicePicAlertBox);
            uploadServicePicAlertBox.show();
            uploadServicePicAlertBox.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        }

    }

    public void removeImage(View v) {
        isImgUploaded = false;
        isFrom = "";
        selectedImagePath = "";
        ((ImageView) uploadImgAlertDialog.findViewById(R.id.uploadImgVIew)).setImageDrawable(null);
        uploadImgAlertDialog.findViewById(R.id.camImgVIew).setVisibility(View.VISIBLE);
        uploadImgAlertDialog.findViewById(R.id.chooseFileTitleTxt).setVisibility(View.VISIBLE);


        uploadImgArea.setClickable(true);
        clearImg.setVisibility(View.GONE);
    }


    private void showDeliveryPreferences(final Context mContext) {

        showDeliveryPreferenceAlertBox = new Dialog(mContext, R.style.Theme_Dialog);
        showDeliveryPreferenceAlertBox.requestWindowFeature(Window.FEATURE_NO_TITLE);

        showDeliveryPreferenceAlertBox.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        showDeliveryPreferenceAlertBox.setContentView(R.layout.design_delivery_preferences);
        showDeliveryPreferenceAlertBox.setCancelable(false);

        MTextView titleTxt = showDeliveryPreferenceAlertBox.findViewById(R.id.titleTxt);
        final RelativeLayout backArea = showDeliveryPreferenceAlertBox.findViewById(R.id.backArea);
        final RecyclerView preferenceList = showDeliveryPreferenceAlertBox.findViewById(R.id.preferenceList);
        MButton btn_type2 = ((MaterialRippleLayout) showDeliveryPreferenceAlertBox.findViewById(R.id.btn_type2)).getChildView();

        titleTxt.setText(vTitle);
        backArea.setVisibility(View.GONE);

        MoreInstructionAdapter moreInstructionAdapter = new MoreInstructionAdapter(getActContext(), instructionslit, map -> {

        });
        preferenceList.setAdapter(moreInstructionAdapter);


        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_OK"));
        btn_type2.setId(Utils.generateViewId());

        btn_type2.setOnClickListener(view -> MyApp.getInstance().restartWithGetDataApp());

        backArea.setVisibility(View.GONE);

        backArea.setOnClickListener(view -> closeShowDeliveryPreferenceAlertBox());

        LayoutDirection.setLayoutDirection(showDeliveryPreferenceAlertBox);
        showDeliveryPreferenceAlertBox.show();

    }

    private void orderPickedUpOrDeliver(String billAmount, boolean b) {

        InternetConnection intCheck = new InternetConnection(getActContext());

        if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
            generalFunc.showGeneralMessage("",
                    generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
            return;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateOrderStatusDriver");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("orderStatus", isDeliver ? "OrderDelivered" : "OrderPickedup");
        parameters.put("iOrderId", iOrderId);
        parameters.put("iTripid", tripId);
        parameters.put("billAmount", billAmount);
        parameters.put("UserType", Utils.app_type);
        parameters.put("eSystem", Utils.eSystem_Type);
        if (eBuyAnyService.equalsIgnoreCase("Yes")) {
            parameters.put("genieWaitingForUserApproval", "Yes");
        }
        parameters.put("eSystem", Utils.eSystem_Type);

        if (userLocation != null) {
            parameters.put("vLatitude", "" + userLocation.getLatitude());
            parameters.put("vLongitude", "" + userLocation.getLongitude());
        }
        if (GetLocationUpdates.getInstance().getLastLocation() != null) {
            Location lastLocation = GetLocationUpdates.getInstance().getLastLocation();

            parameters.put("vLatitude", "" + lastLocation.getLatitude());
            parameters.put("vLongitude", "" + lastLocation.getLongitude());
        }


        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> submitProofResponse(responseString, b));

    }

    private void OrderImageUpload(String eImgSkip) {
        InternetConnection intCheck = new InternetConnection(getActContext());

        if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
            generalFunc.showGeneralMessage("",
                    generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
            return;
        }
        if (!TextUtils.isEmpty(isFrom)) {
            HashMap<String, String> paramsList = new HashMap<String, String>() {{
                put("type", "OrderImageUpload");
                put("iOrderId", iOrderId);
                put("iTripid", tripId);
                put("eImgSkip", eImgSkip);
                put("UserType", Utils.app_type);
                put("eSystem", Utils.eSystem_Type);
                put("iMemberId", generalFunc.getMemberId());
                put("MemberType", Utils.app_type);
                put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
                put("GeneralUserType", Utils.app_type);
                put("GeneralMemberId", generalFunc.getMemberId());
            }};

            UploadProfileImage uploadProfileImage = new UploadProfileImage(LiveTrackOrderDetailActivity.this, selectedImagePath, Utils.TempProfileImageName, paramsList, "");
            uploadProfileImage.execute(false, generalFunc.retrieveLangLBl("", "LBL_GENIE_IMAGE_UPLOADING"));
        } else {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "OrderImageUpload");
            parameters.put("iOrderId", iOrderId);
            parameters.put("iTripid", tripId);
            parameters.put("UserType", Utils.app_type);
            parameters.put("eImgSkip", eImgSkip);
            parameters.put("eSystem", Utils.eSystem_Type);

            ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
                closeuploadServicePicAlertBox();
                submitProofResponse(responseString, false);
            });

        }
    }

    public void handleImgUploadResponse(String responseString, String imageUploadedType) {

        if (responseString != null && !responseString.equals("")) {
            if (eBuyAnyService.equalsIgnoreCase("Yes")) {
                String msgTxt = generalFunc.retrieveLangLBl("Your document is uploaded successfully", "LBL_UPLOAD_DOC_SUCCESS");
                if (isGenie || uploadArea.getVisibility() == View.GONE) {
                    msgTxt = generalFunc.retrieveLangLBl("Your document is uploaded successfully", "LBL_ITEM_INFO_UPDATE");

                }

                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    generateAlert.closeAlertBox();

                    if (uploadImgAlertDialog != null) {
                        uploadImgAlertDialog.dismiss();
                        uploadImgAlertDialog = null;
                    }
                    loading_order_item_list.setVisibility(View.VISIBLE);
                    getOrderDetailList(false);
                });
                generateAlert.setContentMessage("", msgTxt);
                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));

                generateAlert.showAlertBox();

            } else {
                submitProofResponse(responseString, false);
            }
        } else {
            generalFunc.showError();
        }
    }

    private void submitProofResponse(String responseString, boolean callImageUpload) {
        JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

        if (responseStringObject != null) {

            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

            if (isDataAvail) {
                /*Release Dialog instances*/
                if (dialog != null) {
                    dialog.dismiss();
                }

                if (generalFunc.getJsonValueStr("DO_RESTART", responseStringObject).equalsIgnoreCase("Yes")) {
                    MyApp.getInstance().restartWithGetDataApp();
                    return;
                }

                if (eBuyAnyService.equalsIgnoreCase("Yes")) {
                    if (generalFunc.getJsonValueStr("genieWaitingForUserApproval", responseStringObject).equalsIgnoreCase("Yes") &&
                            generalFunc.getJsonValueStr("genieUserApproved", responseStringObject).equalsIgnoreCase("No")) {
                        manageGenieRefreshView();
                        orderItemListRecycleAdapter.setEditable(false);

                        orderItemListRecycleAdapter.notifyDataSetChanged();
                        return;
                    }

                    if (!generalFunc.getJsonValueStr("ePaid", responseStringObject).equalsIgnoreCase("Yes")) {
                        if (generalFunc.getJsonValueStr("genieWaitingForUserApproval", responseStringObject).equalsIgnoreCase("Yes") &&
                                generalFunc.getJsonValueStr("genieUserApproved", responseStringObject).equalsIgnoreCase("Yes")) {
                            genieTitletxt.setText(generalFunc.retrieveLangLBl("Waiting for user to make payment", "LBL_GENIE_PAYMENT_WAITING"));
                            orderItemListRecycleAdapter.setEditable(false);
                            manageGenieRefreshView();
                            return;
                        }
                    }
                }


                if (callImageUpload) {
                    takeAndUploadPic(getActContext());
                } else if (isPrefrence) {
                    showDeliveryPreferences(this);
                } else {
                    MyApp.getInstance().restartWithGetDataApp();
                }
            } else {
                String msg_str = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);

                if (generalFunc.getJsonValueStr("itemsAvailability", responseStringObject) != null && generalFunc.getJsonValueStr("itemsAvailability", responseStringObject).equalsIgnoreCase("No")) {

                    PreferenceDailogJava preferenceDailogJava = new PreferenceDailogJava(getActContext());
                    preferenceDailogJava.showPreferenceDialog("", generalFunc.retrieveLangLBl("", "LBL_ITEM_NOT_AVAILABLE_MARKED"), R.drawable.ic_caution, false, generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT")
                            , generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), true);
                    return;
                }

                if (msg_str.equals(Utils.GCM_FAILED_KEY) || msg_str.equals(Utils.APNS_FAILED_KEY) || msg_str.equals("LBL_SERVER_COMM_ERROR")) {
                    generalFunc.restartApp();
                } else {

                    GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setContentMessage("",
                            generalFunc.retrieveLangLBl("", msg_str));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();

                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {

                        generateAlert.closeAlertBox();
                        if (generalFunc.getJsonValueStr("DO_RESTART", responseStringObject).equalsIgnoreCase("Yes")) {
                            MyApp.getInstance().restartWithGetDataApp();
                        }

                    });
                }

            }
        } else {
            generalFunc.showError();
        }
    }

    public void manageGenieRefreshView() {
        bottomGenieLayout.setVisibility(View.VISIBLE);
        bottomLayout.setVisibility(View.GONE);
        footerLayout.setVisibility(View.GONE);

        RelativeLayout.LayoutParams params = new RelativeLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        params.addRule(RelativeLayout.ABOVE, R.id.bottomGenieLayout);
        params.addRule(RelativeLayout.BELOW, R.id.toolbar_include);
        main_layout.setLayoutParams(params);
    }


    public void closeuploadServicePicAlertBox() {
        if (uploadServicePicAlertBox != null) {
            uploadServicePicAlertBox.dismiss();
        }
    }

    public void closeShowDeliveryPreferenceAlertBox() {
        if (showDeliveryPreferenceAlertBox != null) {
            showDeliveryPreferenceAlertBox.dismiss();
        }
    }

    public Context getActContext() {
        return LiveTrackOrderDetailActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(LiveTrackOrderDetailActivity.this);
        if (i == R.id.backImgView) {
            LiveTrackOrderDetailActivity.super.onBackPressed();
        } else if (i == btn_type_refresh.getId()) {
            loading_order_item_list.setVisibility(View.VISIBLE);
            getOrderDetailList(false);

        } else if (i == btn_type2.getId()) {

            if (list == null || list.size() == 0) {
                return;
            }


            if (eBuyAnyService.equalsIgnoreCase("Yes")) {
                if (!checkItemPriceEntered()) {
                    generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("", "LBL_GENIE_CHECK_ITEM_DETAILS"));
                    return;
                }

                if (!checkItemAvailable()) {
                    PreferenceDailogJava preferenceDailogJava = new PreferenceDailogJava(getActContext());
                    preferenceDailogJava.showPreferenceDialog("", generalFunc.retrieveLangLBl("", "LBL_ITEM_NOT_AVAILABLE_MARKED"), R.drawable.ic_caution, false, generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT")
                            , generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), true);
                    return;
                }
            }
            if (isDeliver) {
                if (list.get(0).getePaid().equalsIgnoreCase("Yes")) {
                    BuildOrderStatusConfirmation(false);
                } else {
                    showBillDialog();
                }
            } else {
                if (orderItemListRecycleAdapter != null && !orderItemListRecycleAdapter.areAllTrue()) {
                    generalFunc.showMessage(findViewById(R.id.mainArea), generalFunc.retrieveLangLBl("Please ensure that you have collected all order items from store.", "LBL_COLLECT_ITEMS_MSG_STORE"));
                    return;
                }

                /*Upload Proof Of Arrival If only Photo Upload Pending*/
                if (eBuyAnyService.equalsIgnoreCase("") || eBuyAnyService.equalsIgnoreCase("No")) {

                    if (list.get(0).getePaid().equalsIgnoreCase("Yes") && PickedFromRes.equalsIgnoreCase("No")) {

                        BuildOrderStatusConfirmation(true);

                    } else if (!isDeliver && isPhotoUploaded.equalsIgnoreCase("No") && PickedFromRes.equalsIgnoreCase("Yes")) {
                        takeAndUploadPic(getActContext());
                    } else {
                        BuildOrderStatusConfirmation(true);
                    }
                } else {
                    GenieBuildOrderStatusConfirmation();
                }
            }
        } else if (i == R.id.footerLayout) {
            showBill();
        } else if (i == R.id.callImgView) {
//                getMaskNumber(0);
            if (list == null || list.size() == 0) {
                return;
            }

            callUserArea.performClick();
        } else if (i == R.id.trackUserLocationArea) {
            if (list == null || list.size() == 0) {
                return;
            }
            Bundle bn = new Bundle();
            bn.putString("type", "trackUser");
            bn.putSerializable("TRIP_DATA", data_trip);

            orderDetailDataModel orderDetailDataModel = list.get(0);
            bn.putString("vLattitude", orderDetailDataModel.getUserLatitude());
            bn.putString("vLongitude", orderDetailDataModel.getUserLongitude());
            bn.putString("vAddress", orderDetailDataModel.getUserAddress());
            bn.putString("sourceLatitude", orderDetailDataModel.getRestaurantLattitude());
            bn.putString("sourceLongitude", orderDetailDataModel.getRestaurantLongitude());

            if (!isDeliver) {
                bn.putString("vPhoneNo", orderDetailDataModel.getRestaurantNumber());
            } else {
                bn.putString("vPhoneNo", orderDetailDataModel.getUserPhone());
            }

            bn.putString("vVehicleType", orderDetailDataModel.getvVehicleType());
            bn.putString("vName", orderDetailDataModel.getUserName());
            bn.putString("vImage", data_trip.get("PPicName"));
            bn.putString("callid", data_trip.get("PassengerId"));
            new ActUtils(getActContext()).startActWithData(TrackOrderActivity.class, bn);


        } else if (i == R.id.callUserArea) {
            if (list == null || list.size() == 0) {
                return;
            }
            boolean isStore = !isDeliver;
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(isStore ? list.get(0).getRestaurantId() : data_trip.get("PassengerId"))
                    .setPhoneNumber(isStore ? list.get(0).getRestaurantNumber() : list.get(0).getUserPhone())
                    .setToMemberType(isStore ? Utils.CALLTOSTORE : Utils.CALLTOPASSENGER)
                    .setToMemberName(isStore ? list.get(0).getRestaurantName() : data_trip.get("PName"))
                    .setToMemberImage(isStore ? list.get(0).getRestaurantImage() : data_trip.get("PPicName"))
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(data_trip.get("iTripId"))
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.OTHER);
        }
    }


    boolean checkItemPriceEntered() {
        boolean isEntered = true;
        for (int i = 0; i < subItemList.size(); i++) {
            if (subItemList.get(i).getItemDetailsUpdated().equalsIgnoreCase("No")) {
                isEntered = false;
                break;

            }
        }
        return isEntered;
    }

    boolean checkItemAvailable() {
        boolean itemAvailble = false;
        for (int i = 0; i < subItemList.size(); i++) {
            if (subItemList.get(i).geteItemAvailable().equalsIgnoreCase("Yes")) {
                itemAvailble = true;
                break;
            }
        }
        return itemAvailble;
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        this.menu = menu;
        if (menu == null) {
            return false;
        }
        if (!isFirst) {
            isFirst = true;
            MenuInflater menuInflater = getMenuInflater();
            menuInflater.inflate(R.menu.live_task_activity, menu);
        }


        menu.findItem(R.id.menu_user_call).setTitle(generalFunc.retrieveLangLBl("", "LBL_CALL_TO_USER"));
        menu.findItem(R.id.menu_user_message).setTitle(generalFunc.retrieveLangLBl("", "LBL_MESSAGE_TO_USER"));
        menu.findItem(R.id.cancel_order).setTitle(generalFunc.retrieveLangLBl("", "LBL_CANCEL_ORDER"));
        menu.findItem(R.id.contact_us).setTitle(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        menu.findItem(R.id.intruction_store).setTitle(generalFunc.retrieveLangLBl("", "LBL_VIEW_SPEC_INS_FOR_STORE"));

        if (!vInstruction.equalsIgnoreCase("") && eAutoaccept.equalsIgnoreCase("Yes")) {
            menu.findItem(R.id.intruction_store).setVisible(true);
        } else {
            menu.findItem(R.id.intruction_store).setVisible(false);

        }

        if (eBuyAnyService.equalsIgnoreCase("") || eBuyAnyService.equalsIgnoreCase("No")) {
            menu.findItem(R.id.menu_user_call).setVisible(false);
            menu.findItem(R.id.menu_user_message).setVisible(false);
            String cancel_Order = generalFunc.getJsonValueStr("ENABLE_CANCEL_DRIVER_ORDER", obj_userProfile);
            if (cancel_Order != null && cancel_Order.equalsIgnoreCase("Yes")) {

                menu.findItem(R.id.cancel_order).setVisible(true);
            } else {
                menu.findItem(R.id.cancel_order).setVisible(false);
            }
        } else {
            menu.findItem(R.id.menu_user_call).setVisible(true);
            menu.findItem(R.id.menu_user_message).setVisible(true);

            if (generalFunc.getJsonValueStr("ENABLE_CANCEL_DRIVER_ORDER", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_CANCEL_DRIVER_ORDER", obj_userProfile).equalsIgnoreCase("Yes")) {
                menu.findItem(R.id.cancel_order).setVisible(true);
            } else {
                menu.findItem(R.id.cancel_order).setVisible(false);
            }
        }

        if (menu.hasVisibleItems()) {
            LinearLayout.LayoutParams buttonLayoutParams = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.MATCH_PARENT);

            if (generalFunc.isRTLmode()) {
                buttonLayoutParams.setMargins(0, 0, 100, 0);
            } else {
                buttonLayoutParams.setMargins((int) getResources().getDimension(R.dimen._45sdp), 0, 0, 0);
            }
            titleTxt.setLayoutParams(buttonLayoutParams);
        }

        Utils.setMenuTextColor(menu.findItem(R.id.menu_user_call), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_user_message), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.cancel_order), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.contact_us), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.intruction_store), getResources().getColor(R.color.black));
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        int itemId = item.getItemId();
        if (itemId == R.id.menu_user_call) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(data_trip.get("PassengerId"))
                    .setPhoneNumber(list.get(0).getUserPhone())
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(data_trip.get("PName"))
                    .setToMemberImage(data_trip.get("PPicName"))
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(data_trip.get("iTripId"))
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.OTHER);
            return true;
        } else if (itemId == R.id.menu_user_message) {
            MediaDataProvider mDataProvider1 = new MediaDataProvider.Builder()
                    .setToMemberId(data_trip.get("PassengerId"))
                    .setPhoneNumber(list.get(0).getUserPhone())
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(data_trip.get("PName"))
                    .setToMemberImage(data_trip.get("PPicName"))
                    .setBookingNo(data_trip.get("vOrderNo"))
                    .setOrderId(data_trip.get("iOrderId"))
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(data_trip.get("iTripId"))
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider1, CommunicationManager.TYPE.CHAT);
            return true;
        } else if (itemId == R.id.cancel_order) {
            showDeclineReasonsAlert();
            return true;
        } else if (itemId == R.id.contact_us) {
            new ActUtils(getActContext()).startAct(ContactUsActivity.class);
            return true;
        } else if (itemId == R.id.intruction_store) {
            PreferenceDailogJava preferenceDailogJava = new PreferenceDailogJava(getActContext());
            preferenceDailogJava.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_VIEW_SPEC_INS_FOR_STORE"), vInstruction, R.drawable.ic_notes, false,
                    generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), "", false);
            return true;
        }


        return true;
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        if (isGenie && uploadImgAlertDialog == null) {
            return;
        }
        if (!isGenie && uploadServicePicAlertBox == null) {
            return;
        }
        this.selectedImagePath = mFilePath;

        try {
            BitmapFactory.Options options = new BitmapFactory.Options();
            options.inJustDecodeBounds = true;
            BitmapFactory.decodeFile(mFilePath, options);

            if (isGenie) {
                clearImg.setVisibility(View.VISIBLE);
                uploadImgArea.setClickable(false);

                new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), (uploadImgAlertDialog.findViewById(R.id.uploadImgVIew))).build();
                (uploadImgAlertDialog.findViewById(R.id.mCardView)).setBackgroundResource(R.drawable.update_border);
            } else {
                new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), (uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew))).build();
            }
        } catch (Exception e) {
            if (isGenie) {
                clearImg.setVisibility(View.VISIBLE);
                uploadImgArea.setClickable(false);
                new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), (uploadImgAlertDialog.findViewById(R.id.uploadImgVIew))).build();
                (uploadImgAlertDialog.findViewById(R.id.mCardView)).setBackgroundResource(R.drawable.update_border);
            } else {
                new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), (uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew))).build();
            }
        }
        if (isGenie) {
            uploadImgAlertDialog.findViewById(R.id.camImgVIew).setVisibility(View.GONE);
            uploadImgAlertDialog.findViewById(R.id.chooseFileTitleTxt).setVisibility(View.GONE);
        } else {
            uploadServicePicAlertBox.findViewById(R.id.camImgVIew).setVisibility(View.GONE);
            uploadServicePicAlertBox.findViewById(R.id.ic_add).setVisibility(View.GONE);
        }
    }
}