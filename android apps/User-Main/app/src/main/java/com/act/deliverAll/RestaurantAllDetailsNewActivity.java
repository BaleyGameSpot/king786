package com.act.deliverAll;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.os.SystemClock;
import android.util.DisplayMetrics;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.WindowManager;
import android.view.animation.AlphaAnimation;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.ScrollView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatDialog;
import androidx.cardview.widget.CardView;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;
import androidx.viewpager.widget.ViewPager;

import com.ViewPagerCards.CardPagerAdapter;
import com.ViewPagerCards.ShadowTransformer;
import com.act.PrescriptionActivity;
import com.activity.ParentActivity;
import com.adapter.files.deliverAll.MenuAdapter;
import com.adapter.files.deliverAll.RecommendedListAdapter;
import com.adapter.files.deliverAll.RestaurantRecomMenuAdapter;
import com.adapter.files.deliverAll.RestaurantmenuAdapter;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.SafetyDialog;
import com.google.android.material.appbar.AppBarLayout;
import com.google.android.material.appbar.CollapsingToolbarLayout;
import com.buddyverse.main.R;
import com.kyleduo.switchbutton.SwitchButton;
import com.like.LikeButton;
import com.like.OnLikeListener;
import com.realmModel.Cart;
import com.realmModel.Options;
import com.realmModel.Topping;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import io.realm.Realm;
import io.realm.RealmResults;

public class RestaurantAllDetailsNewActivity extends ParentActivity implements AppBarLayout.OnOffsetChangedListener, RestaurantmenuAdapter.OnItemClickListener, RestaurantRecomMenuAdapter.OnItemClickListener, SwipeRefreshLayout.OnRefreshListener {

    private static final float PERCENTAGE_TO_SHOW_TITLE_AT_TOOLBAR = 0.30f;
    private static final int ALPHA_ANIMATIONS_DURATION = 200;
    ImageView backarrorImgView;
    TextView titleTxtView;
    RecyclerView resMenuRecyclerview, resRecomMenuRecyclerview, menuRecyclerview;
    MTextView restNametxt, restcusineNameTxt, ratingCntTxt, ratingdecTxt, deliveryValTxt, deliveryLBLTxt, minOrderValTxt, minOrderLBLTxt, offerMsgTxt, foodTypetxt;
    LinearLayout offerArea, VegNovegFilterArea;
    ImageView infoImage, rightFoodImgView;
    String vCompany = "";
    String fMinOrderValue = "0";
    String iMaxItemQty = "0";

    ArrayList<HashMap<String, String>> recommandlist = new ArrayList<>();
    ArrayList<HashMap<String, String>> menuList = new ArrayList<>();
    ArrayList<HashMap<String, String>> list = new ArrayList<>();

    RestaurantmenuAdapter restaurantmenuAdapter;
    RestaurantRecomMenuAdapter restaurantRecomMenuAdapter;
    MenuAdapter menuAdapter;
    com.andremion.counterfab.CounterFab cartFoodImgView;
    RealmResults<Cart> realmCartList;
    LinearLayout bottomCartView, dialogsLayout;
    MTextView itemNpricecartTxt, viewCartTxt;
    RelativeLayout dialogsLayoutArea;
    CardView informationDesignCardView, ratingDesignCardView;
    MTextView openingHourTxt, timeHTxt, timeVTxt, titleDailogTxt, addressDailogTxt, timeSatTxt, timeVSatTxt;
    MButton closeBtn;
    SwitchButton vegNonVegSwitch;
    RelativeLayout fabMenuLayout;
    ArrayList<Cart> cartList;
    double finalTotal = 0;
    int item = 0;
    String CurrencySymbol = "";
    String isSearchVeg = "No";
    View menubackView;
    LinearLayout minOrderArea;
    View minView;
    boolean isLike = false;
    String isFavStore = "No";
    String isVeg = "";
    boolean isFavChange = false;
    String iCompanyId = "";
    MTextView reCommTxt;
    LinearLayout menuArea;
    LinearLayout main_layout;
    long mLastClickTime = 0;
    JSONArray restaurant_Arr = new JSONArray();
    JSONArray Recomendation_Arr = new JSONArray();
    String LBL_ADD = "";
    String LBL_RECOMMENDED = "";
    String LBL_VEG_ONLY = "";
    String LBL_MINIMUM_ORDER_NOTE = "";
    int getNoOfColumns;
    ProgressBar mProgressBar;
    int totalSize = 0;
    JSONObject companyDetails;
    View bannerArea;
    ViewPager bannerViewPager;
    RelativeLayout backCardArea;
    LinearLayout ratingArea;
    ImageView ratingCancel;
    SimpleRatingBar ratingBar;
    MTextView orderHotelName;
    MTextView resSsafetydetailTxt;
    MTextView resSsafetymeasureTxt;
    ImageView safetyImage;
    ImageView arrowImage;
    LinearLayout safetylayout;
    int screenWidth;
    LinearLayout openingHrArea;
    ScrollView newTimeSlotsArea;
    LinearLayout oldTimeSlotsArea;
    ArrayList<HashMap<String, String>> slotsArray = new ArrayList<>();
    int first = 0;
    int scrollsate = 1;
    List<String> list_h = new ArrayList<>();
    List<String> list_v = new ArrayList<>();
    String ENABLE_STORE_WISE_BANNER;
    String Restaurant_Safety_Status;
    String Restaurant_Safety_URL;
    AppCompatDialog recommendedDialog;
    int MenuSelectPos = -1;
    boolean topClick = false;
    boolean ischeckPrescriptionClick = false;
    private boolean mIsTheTitleVisible = false;
    private CollapsingToolbarLayout collapsingToolbarLayout = null;
    private AppBarLayout appbar;
    //Fav Store Features
    private LikeButton likeButton;
    private CardPagerAdapter mCardAdapter;
    private ShadowTransformer mCardShadowTransformer;
    private SwipeRefreshLayout swipeRefreshLayout;
    private String LBL_CLOSED_TXT;

    private boolean isLoadLangDone = false;
    private int foodMenuIdPos = -1;

    public static float dpToPixels(int dp, Context context) {
        return dp * (context.getResources().getDisplayMetrics().density);
    }

    public static void startAlphaAnimation(View v, long duration, int visibility) {
        AlphaAnimation alphaAnimation = (visibility == View.VISIBLE)
                ? new AlphaAnimation(0f, 1f)
                : new AlphaAnimation(1f, 0f);

        alphaAnimation.setDuration(duration);
        alphaAnimation.setFillAfter(true);
        v.startAnimation(alphaAnimation);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_restaurant_all_details_new);

        mProgressBar = findViewById(R.id.mProgressBar);
        mProgressBar.setVisibility(View.VISIBLE);

        backCardArea = findViewById(R.id.backCardArea);
        backCardArea.setVisibility(View.GONE);
        main_layout = findViewById(R.id.main_layout);
        main_layout.setVisibility(View.GONE);
        menuArea = findViewById(R.id.menuArea);
        menuArea.setVisibility(View.GONE);

        infoImage = (ImageView) findViewById(R.id.infoImage);
        infoImage .setVisibility(View.GONE);
        likeButton = (LikeButton) findViewById(R.id.likeButton);
        likeButton.setVisibility(View.GONE);

        if (getIntent().getBooleanExtra("getLanguageLabel", false)) {
            if (generalFunc.prefHasKey(Utils.iServiceId_KEY) && Utils.checkText(generalFunc.retrieveValue(Utils.iServiceId_KEY))) {
                new Handler(Looper.getMainLooper()).postDelayed(() -> {
                    //
                    DeliverAllServiceCategory.getInstance().getLanguageLabelServiceWise(generalFunc.retrieveValue(Utils.iServiceId_KEY), (allData, dataDic) -> {
                        if (isFinishing()) {
                            return;
                        }
                        //
                        HashMap<String, String> storeData = new HashMap<>();
                        storeData.put(Utils.languageLabelsKey, dataDic);
                        storeData.put(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", allData));
                        storeData.put(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", allData));
                        storeData.put(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", allData));
                        generalFunc.storeData(storeData);
                        GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
                        Utils.setAppLocal(MyApp.getInstance().getCurrentAct());

                        onCreateContentView();
                        onResume();

                        //
                        isLoadLangDone = true;
                    });
                }, 77);
            } else {
                generalFunc.showError(true);
            }
            // TODO : Please set All Code onCreateContentView()
        } else {
            onCreateContentView();
        }
    }

    private void onCreateContentView() {
        generalFunc.runGAC();

        iCompanyId = getIntent().getStringExtra("iCompanyId");


        resSsafetydetailTxt = findViewById(R.id.resSsafetydetailTxt);
        resSsafetymeasureTxt = findViewById(R.id.resSsafetymeasureTxt);
        safetyImage = findViewById(R.id.safetyImage);
        arrowImage = findViewById(R.id.arrowImage);

        if (generalFunc.isRTLmode()) {
            arrowImage.setRotationY(180);
        }
        safetylayout = findViewById(R.id.safetylayout);

        DisplayMetrics displayMetrics = new DisplayMetrics();
        getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
        screenWidth = displayMetrics.widthPixels;

        swipeRefreshLayout = (SwipeRefreshLayout) findViewById(R.id.swipe_refresh_layout);
        swipeRefreshLayout.setOnRefreshListener(this);
        ratingArea = (LinearLayout) findViewById(R.id.ratingArea);
        ratingCancel = (ImageView) findViewById(R.id.ratingCancel);
        ratingBar = (SimpleRatingBar) findViewById(R.id.ratingBar);
        orderHotelName = (MTextView) findViewById(R.id.orderHotelName);
        ratingCancel.setOnClickListener(v -> ratingArea.setVisibility(View.GONE));

        if (generalFunc.getJsonValueStr("LastOrderFoodDetailRatingShow", obj_userProfile).equalsIgnoreCase("Yes")) {
            ratingBar.setPressedFillColor(ContextCompat.getColor(getActContext(), R.color.white));
        }
        ratingBar.setOnClickListener(v -> {

            Bundle bn = new Bundle();

            bn.putFloat("rating", ratingBar.getRating());

            if (generalFunc.getJsonValueStr("LastOrderFoodDetailRatingShow", obj_userProfile).equalsIgnoreCase("Yes")) {
                bn.putBoolean("IS_NEW", true);
            } else {
                bn.putBoolean("IS_NEW", false);
            }
            bn.putString("iOrderId", generalFunc.getJsonValueStr("LastOrderId", obj_userProfile));
            bn.putString("vOrderNo", generalFunc.getJsonValueStr("LastOrderNo", obj_userProfile));
            bn.putString("driverName", generalFunc.getJsonValueStr("LastOrderDriverName", obj_userProfile));
            bn.putString("vCompany", generalFunc.getJsonValueStr("LastOrderCompanyName", obj_userProfile));
            bn.putString("eTakeaway", generalFunc.getJsonValueStr("LastOrderTakeaway", obj_userProfile));

            new ActUtils(getActContext()).startActWithData(FoodRatingActivity.class, bn);
            ratingArea.setVisibility(View.GONE);
        });

        bannerArea = findViewById(R.id.bannerArea);
        bannerViewPager = (ViewPager) findViewById(R.id.bannerViewPager);


        menuArea = findViewById(R.id.menuArea);
        main_layout = findViewById(R.id.main_layout);
        backCardArea = findViewById(R.id.backCardArea);
        backCardArea.setVisibility(View.GONE);
        main_layout.setVisibility(View.GONE);
        menuArea.setVisibility(View.GONE);
        new CreateRoundedView(Color.parseColor("#ffffff"), 30, 2, Color.parseColor("#cfcfcf"), main_layout);
        reCommTxt = findViewById(R.id.reCommTxt);
        backarrorImgView = findViewById(R.id.backArrowImgView);

        menubackView = findViewById(R.id.menubackView);
        addToClickHandler(menubackView);

        vegNonVegSwitch = findViewById(R.id.vegNonVegSwitch);
        dialogsLayout = findViewById(R.id.dialogsLayout);
        openingHrArea = findViewById(R.id.openingHrArea);
        oldTimeSlotsArea = findViewById(R.id.oldTimeSlotsArea);
        newTimeSlotsArea = findViewById(R.id.newTimeSlotsArea);


        dialogsLayoutArea = findViewById(R.id.dialogsLayoutArea);
        fabMenuLayout = findViewById(R.id.fabMenuLayout);
        mProgressBar = findViewById(R.id.mProgressBar);

//        closeBtn = findViewById(R.id.closeBtn);
        closeBtn = ((MaterialRippleLayout) findViewById(R.id.closeBtn)).getChildView();
        addToClickHandler(closeBtn);

        informationDesignCardView = findViewById(R.id.informationDesignCardView);
        informationDesignCardView.setVisibility(View.GONE);
        openingHourTxt = findViewById(R.id.openingHourTxt);
        titleDailogTxt = findViewById(R.id.titleDailogTxt);
        addressDailogTxt = findViewById(R.id.addressDailogTxt);
        timeHTxt = findViewById(R.id.timeHTxt);
        timeVSatTxt = findViewById(R.id.timeVSatTxt);
        timeSatTxt = findViewById(R.id.timeSatTxt);
        timeVTxt = findViewById(R.id.timeVTxt);
        ratingDesignCardView = findViewById(R.id.ratingDesignCardView);
        rightFoodImgView = findViewById(R.id.rightFoodImgView);
        addToClickHandler(rightFoodImgView);
        addToClickHandler(backarrorImgView);
//        searchImgView = findViewById(R.id.searchImgView);
        titleTxtView = findViewById(R.id.titleTxtView);
        collapsingToolbarLayout = (CollapsingToolbarLayout) findViewById(R.id.collapsing_toolbar);
        collapsingToolbarLayout.setTitle("");
        appbar = (AppBarLayout) findViewById(R.id.app_bar_layout);
        appbar.addOnOffsetChangedListener(this);
        resMenuRecyclerview = (RecyclerView) findViewById(R.id.resMenuRecyclerview);
        resRecomMenuRecyclerview = (RecyclerView) findViewById(R.id.resRecomMenuRecyclerview);
        menuRecyclerview = (RecyclerView) findViewById(R.id.menuRecyclerview);
        //  nestedscroll = findViewById(R.id.nestedscroll);
        restNametxt = (MTextView) findViewById(R.id.restNametxt);
        restNametxt.setSelected(true);
        restcusineNameTxt = (MTextView) findViewById(R.id.restcusineNameTxt);
        ratingCntTxt = (MTextView) findViewById(R.id.ratingCntTxt);
        ratingdecTxt = (MTextView) findViewById(R.id.ratingdecTxt);
        deliveryValTxt = (MTextView) findViewById(R.id.deliveryValTxt);
        deliveryLBLTxt = (MTextView) findViewById(R.id.deliveryLBLTxt);
        minOrderValTxt = (MTextView) findViewById(R.id.minOrderValTxt);
        minOrderLBLTxt = (MTextView) findViewById(R.id.minOrderLBLTxt);
        offerMsgTxt = (MTextView) findViewById(R.id.offerMsgTxt);
        foodTypetxt = (MTextView) findViewById(R.id.foodTypetxt);
        offerArea = (LinearLayout) findViewById(R.id.offerArea);
        VegNovegFilterArea = (LinearLayout) findViewById(R.id.VegNovegFilterArea);
        infoImage = (ImageView) findViewById(R.id.infoImage);
        infoImage .setVisibility(View.GONE);
        addToClickHandler(infoImage);
        cartFoodImgView = findViewById(R.id.cartFoodImgView);
//        cartFoodImgView.setOnClickListener(new setOnClickList());
        bottomCartView = (LinearLayout) findViewById(R.id.bottomCartView);
        addToClickHandler(bottomCartView);
        itemNpricecartTxt = (MTextView) findViewById(R.id.itemNpricecartTxt);
        viewCartTxt = (MTextView) findViewById(R.id.viewCartTxt);

        minOrderArea = (LinearLayout) findViewById(R.id.minOrderArea);
        minView = (View) findViewById(R.id.minView);

        //Fav Store Features
        likeButton = (LikeButton) findViewById(R.id.likeButton);
        likeButton.setVisibility(View.GONE);
        likeButton.setOnLikeListener(new OnLikeListener() {


            @Override
            public void liked(LikeButton likeButton) {
                if (SystemClock.elapsedRealtime() - mLastClickTime < 1000) {
                    return;
                }
                mLastClickTime = SystemClock.elapsedRealtime();
                isFavChange = true;
                isLike = true;
                isFavStore = "Yes";
                getRestaurantDetails(isVeg, true);
            }

            @Override
            public void unLiked(LikeButton likeButton) {

                if (SystemClock.elapsedRealtime() - mLastClickTime < 1000) {

                    return;
                }
                mLastClickTime = SystemClock.elapsedRealtime();
                isFavChange = true;
                isLike = false;
                isFavStore = "No";
                // addRemoveFav();
                getRestaurantDetails(isVeg, true);

            }
        });

        getRestaurantDetails("", false);
        setLabel();
        getNoOfColumns = getNumOfColumns();

        setParentCategoryLayoutManager();

        vegNonVegSwitch.setOnCheckedChangeListener((buttonView, isChecked) -> {

            if (isChecked) {
                isVeg = "Yes";
                isSearchVeg = isVeg;
                vegNonVegSwitch.setThumbColorRes(R.color.Green);
            } else {
                isVeg = "No";
                isSearchVeg = isVeg;
                vegNonVegSwitch.setThumbColorRes(android.R.color.holo_red_dark);
            }


            getRestaurantDetails(isVeg, false);

        });


        if (generalFunc.isRTLmode()) {
            backarrorImgView.setRotation(180);
        }


        resMenuRecyclerview.addOnScrollListener(new RecyclerView.OnScrollListener() {
                                                    @Override
                                                    public void onScrollStateChanged(@NonNull RecyclerView recyclerView, int newState) {
                                                        super.onScrollStateChanged(recyclerView, newState);

                                                    }

                                                    @Override
                                                    public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                                                        super.onScrolled(recyclerView, dx, dy);
                                                        if (topClick) {
                                                            topClick = false;
                                                            return;
                                                        }
                                                        LinearLayoutManager layoutManager = ((LinearLayoutManager) resMenuRecyclerview.getLayoutManager());
                                                        try {
                                                            int x1 = layoutManager.findLastCompletelyVisibleItemPosition();
                                                            int x2 = layoutManager.findFirstCompletelyVisibleItemPosition();
                                                            if (first != x1) {
                                                                if (list_h.contains(list_v.get(first))) {
                                                                    int matchPosition = list_h.indexOf(list_v.get(first));
                                                                    if (scrollsate == 1) {
                                                                        scrollMenu(Math.max(matchPosition, 0));
                                                                    } else {
                                                                        scrollMenu(Math.max(matchPosition - 1, 0));
                                                                    }
                                                                }
                                                            }
                                                            if (scrollsate == 2 && x2 == 0) {
                                                                scrollMenu(0);
                                                            }
                                                            if (scrollsate == 1 && x1 == list_v.size() - 1) {
                                                                scrollMenu(list_h.size() - 1);
                                                            }
                                                            first = Math.max(x1, 0);
                                                            if (dy > 0) {
                                                                scrollsate = 1;
                                                            } else {
                                                                scrollsate = 2;
                                                            }
                                                        } catch (Exception e) {
                                                            Logger.e("Exception", "::" + e.getMessage());
                                                        }
                                                    }
                                                }

        );


        ratingArea.setVisibility(View.GONE);
        swipeRefreshLayout.setRefreshing(false);
        swipeRefreshLayout.setEnabled(false);
        if (generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
            likeButton.setVisibility(View.GONE);
            swipeRefreshLayout.setEnabled(true);
            titleTxtView.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
            titleTxtView.setGravity(Gravity.CENTER);
            titleTxtView.setVisibility(View.VISIBLE);

            String Ratings_From_DeliverAll = generalFunc.getJsonValueStr("Ratings_From_DeliverAll", obj_userProfile);

            if (Ratings_From_DeliverAll != null && !Ratings_From_DeliverAll.equalsIgnoreCase("") && !Ratings_From_DeliverAll.equalsIgnoreCase("Done")) {
                ratingArea.setVisibility(View.VISIBLE);
                manageRecyclviewHeight();
                orderHotelName.setText(generalFunc.getJsonValueStr("LastOrderCompanyName", obj_userProfile));
            }
        }


        if (generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
            main_layout.setVisibility(View.GONE);
        }
    }

    public void manageRecyclviewHeight() {
        LinearLayout.LayoutParams marginLayoutParams = new LinearLayout.LayoutParams(resMenuRecyclerview.getLayoutParams());

        if (ratingArea.getVisibility() == View.VISIBLE) {
            if (generalFunc.isRTLmode()) {
                marginLayoutParams.setMargins((int) getResources().getDimension(R.dimen._15sdp), 0, 0, (int) getResources().getDimension(R.dimen._60sdp));
            } else {
                marginLayoutParams.setMargins(0, 0, (int) getResources().getDimension(R.dimen._15sdp), (int) getResources().getDimension(R.dimen._60sdp));
            }

        } else {
            if (generalFunc.isRTLmode()) {
                marginLayoutParams.setMargins((int) getResources().getDimension(R.dimen._15sdp), 0, 0, 0);
            } else {
                marginLayoutParams.setMargins(0, 0, (int) getResources().getDimension(R.dimen._15sdp), 0);
            }
        }

        resMenuRecyclerview.setLayoutParams(marginLayoutParams);
    }

    public void setLabel() {
        deliveryLBLTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_TIME"));
//        viewCartTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_CART"));
        viewCartTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHECKOUT"));
        openingHourTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OPENING_HOURS"));

        closeBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CLOSE_TXT"));
        minOrderLBLTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FOR_ONE"));

        LBL_RECOMMENDED = generalFunc.retrieveLangLBl("", "LBL_RECOMMENDED");
        reCommTxt.setText(LBL_RECOMMENDED);

        LBL_VEG_ONLY = generalFunc.retrieveLangLBl("", "LBL_VEG_ONLY");
        LBL_MINIMUM_ORDER_NOTE = generalFunc.retrieveLangLBl("", "LBL_MINIMUM_ORDER_NOTE");
        LBL_ADD = generalFunc.retrieveLangLBl("", "LBL_ADD");
        LBL_CLOSED_TXT = generalFunc.retrieveLangLBl("", "LBL_CLOSED_TXT");
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (isLoadLangDone) {
            DeliverAllServiceCategory.getInstance().getLanguageLabelServiceWise(generalFunc.retrieveValue("DEFAULT_SERVICE_CATEGORY_ID"), (allData, dataDic) -> {

                HashMap<String, String> storeData = new HashMap<>();
                storeData.put(Utils.languageLabelsKey, dataDic);
                storeData.put(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", allData));
                storeData.put(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", allData));
                storeData.put(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", allData));
                generalFunc.storeData(storeData);
                GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
                Utils.setAppLocal(MyApp.getInstance().getCurrentAct());
            });
        }
        try {

            realmCartList = getCartData();
            cartList = new ArrayList<>(realmCartList);

            int listSize = realmCartList.size();
            if (listSize > 0) {

                int cnt = 0;
                for (int i = 0; i < listSize; i++) {
                    cnt = cnt + GeneralFunctions.parseIntegerValue(0, realmCartList.get(i).getQty());
                }

                cartFoodImgView.setCount(cnt);

                bottomCartView.setVisibility(View.VISIBLE);
                findViewById(R.id.blankView).setVisibility(View.VISIBLE);
                calculateItemNprice();

            } else {
                cartFoodImgView.setImageDrawable(getResources().getDrawable(R.drawable.cart_new));

                cartFoodImgView.setCount(0);
                findViewById(R.id.blankView).setVisibility(View.GONE);
                bottomCartView.setVisibility(View.GONE);
            }
        } catch (Exception e) {
            Logger.d("Exception", "::" + e.toString());
        }
    }

    public void calculateItemNprice() {
        finalTotal = 0;
        item = 0;
        int cartsize = cartList.size();
        for (int i = 0; i < cartsize; i++) {
            int pos = i;

            Cart cartData = cartList.get(pos);
            Cart cartPosData = cartList.get(i);
            CurrencySymbol = cartData.getCurrencySymbol();

            double total = GeneralFunctions.parseDoubleValue(0, cartData.getfDiscountPrice());
            String[] selToppingarray = cartPosData.getiToppingId().split(",");
            double toppingPrice = 0;

            int toppingsSize = selToppingarray.length;
            if (selToppingarray != null && toppingsSize > 0) {
                for (int j = 0; j < toppingsSize; j++) {
                    toppingPrice = toppingPrice + GeneralFunctions.parseDoubleValue(0, getToppingPrice(selToppingarray[j]));
                }
            }
            double optionPrice = 0;
            String itemList = "";
            String optionId = cartData.getiOptionId();
            Options options = null;
            try {
                String[] selOptionarray = optionId.split(",");
                int optionSize = selOptionarray.length;

                if (optionId != null && !optionId.equalsIgnoreCase("") && optionSize > 0) {
                    for (String s : selOptionarray) {
                        options = getOptionObject(s);
                        if (options != null) {
                            if (itemList.equalsIgnoreCase("")) {
                                itemList = options.getvOptionName();
                            } else {
                                itemList = itemList + "," + options.getvOptionName();
                            }
                            optionPrice = optionPrice + GeneralFunctions.parseDoubleValue(0, getOptionPrice(s));
                        }
                    }
                }
            } catch (Exception e) {
                if (optionId != null && !optionId.equalsIgnoreCase("")) {
                    options = getOptionObject(optionId);
                    if (options != null) {
                        itemList = options.getvOptionName();
                        optionPrice = GeneralFunctions.parseDoubleValue(0, options.getfUserPrice());
                    }
                }
            }

            String toppingId = cartData.getiToppingId();
            if (toppingId != null && !toppingId.equalsIgnoreCase("")) {
                for (String s : selToppingarray) {
                    Topping topping = getToppingObject(s);
                    if (topping != null) {
                        if (itemList.equalsIgnoreCase("")) {
                            itemList = topping.getvOptionName();
                        } else {
                            itemList = itemList + "," + topping.getvOptionName();
                        }
                    }
                }
            }
            String qty = cartData.getQty();
            if (getIntent().getStringExtra("ispriceshow").equalsIgnoreCase("separate")) {
                if (optionPrice == 0) {
                    total = GeneralFunctions.parseDoubleValue(0, qty) * (toppingPrice + total);
                } else {
                    total = GeneralFunctions.parseDoubleValue(0, qty) * (toppingPrice + optionPrice);
                }
            } else {
                total = GeneralFunctions.parseDoubleValue(0, qty) * (total + toppingPrice + optionPrice);
            }
            item = item + GeneralFunctions.parseIntegerValue(0, cartPosData.getQty());
            finalTotal = finalTotal + total;
        }
        itemNpricecartTxt.setText(generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(finalTotal)), CurrencySymbol, true));
    }

    public String getOptionPrice(String id) {
        String optionPrice = "";
        Realm realm = MyApp.getRealmInstance();
        Options options = realm.where(Options.class).equalTo("iOptionId", id).findFirst();
        if (options != null) {
            return options.getfUserPrice();
        }
        return optionPrice;
    }

    public Options getOptionObject(String id) {
        Realm realm = MyApp.getRealmInstance();
        Options options = realm.where(Options.class).equalTo("iOptionId", id).findFirst();
        if (options != null) {
            return options;
        }
        return null;
    }

    public Topping getToppingObject(String id) {

        Realm realm = MyApp.getRealmInstance();
        Topping topping = realm.where(Topping.class).equalTo("iOptionId", id).findFirst();
        if (topping != null) {
            return topping;
        }
        return topping;

    }

    public String getToppingPrice(String id) {
        String optionPrice = "";

        Realm realm = MyApp.getRealmInstance();
        Topping options = realm.where(Topping.class).equalTo("iOptionId", id).findFirst();

        if (options != null) {
            return options.getfUserPrice();
        }


        return optionPrice;

    }

    public RealmResults<Cart> getCartData() {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Cart.class).findAll();
    }

    public Context getActContext() {
        return RestaurantAllDetailsNewActivity.this;
    }

    @Override
    public void onOffsetChanged(AppBarLayout appBarLayout, int verticalOffset) {
        if (generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
            swipeRefreshLayout.setEnabled(verticalOffset == 0);
        }
        int maxScroll = appBarLayout.getTotalScrollRange();
        float percentage = (float) Math.abs(verticalOffset) / (float) maxScroll;

        if (!generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
            handleToolbarTitleVisibility(percentage);
        }
    }

    public Integer getNumOfColumns() {
        DisplayMetrics displayMetrics = getResources().getDisplayMetrics();
        float dpWidth = (displayMetrics.widthPixels - Utils.dipToPixels(getActContext(), 10)) / displayMetrics.density;
        int noOfColumns = (int) (dpWidth / 170);
        noOfColumns = noOfColumns < 2 ? 2 : noOfColumns;
        noOfColumns = 2;
        return noOfColumns;
    }

    private void setParentCategoryLayoutManager() {
        GridLayoutManager gridLay = new GridLayoutManager(getActContext(), getNoOfColumns);
        gridLay.setSpanSizeLookup(new GridLayoutManager.SpanSizeLookup() {
            @Override
            public int getSpanSize(int position) {
                String type = list.get(position).get("Type");
                if (type != null && !type.equalsIgnoreCase("GRID")) {
                    if (getNoOfColumns > gridLay.getSpanCount()) {
                        return gridLay.getSpanCount();
                    } else {
                        return getNoOfColumns;
                    }
                }
                return 1;
            }
        });
        resMenuRecyclerview.setLayoutManager(gridLay);
    }

    public void getRestaurantDetails(String isVeg, boolean isLikeclick) {
        generalFunc.runGAC();
        mProgressBar.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "GetRestaurantDetails");
        parameters.put("iCompanyId", iCompanyId);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("CheckNonVegFoodType", isVeg);
        if (isLikeclick) {
            parameters.put("eFavStore", isFavStore);
        }
        parameters.put("PassengerLat", getIntent().getStringExtra("lat"));
        parameters.put("PassengerLon", getIntent().getStringExtra("long"));
        parameters.put("eSystem", Utils.eSystem_Type);
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {
            swipeRefreshLayout.setRefreshing(false);
            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            if (responseObj != null) {
                infoImage .setVisibility(View.VISIBLE);
                if (generalFunc.getJsonValueStr("ENABLE_FAVORITE_STORE_MODULE", obj_userProfile).equalsIgnoreCase("Yes") && !generalFunc.getMemberId().equalsIgnoreCase("")) {
                    likeButton.setVisibility(View.VISIBLE);
                } else {
                    likeButton.setVisibility(View.GONE);

                }

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseObj);
                Restaurant_Safety_Status = generalFunc.getJsonValueStr("Restaurant_Safety_Status", responseObj);
                String Restaurant_Safety_Icon = generalFunc.getJsonValueStr("Restaurant_Safety_Icon", responseObj);
                Restaurant_Safety_URL = generalFunc.getJsonValueStr("Restaurant_Safety_URL", responseObj);
                ENABLE_STORE_WISE_BANNER = generalFunc.getJsonValueStr("ENABLE_STORE_WISE_BANNER", obj_userProfile);

                if (Restaurant_Safety_Status.equalsIgnoreCase("Yes") || (ENABLE_STORE_WISE_BANNER != null && ENABLE_STORE_WISE_BANNER.equalsIgnoreCase("Yes") && !Restaurant_Safety_URL.equalsIgnoreCase(""))) {
                    enableSafety(Restaurant_Safety_URL, Restaurant_Safety_Icon);
                }


                if (isDataAvail) {

                    slotsArray.clear();

                    JSONObject message_obj = generalFunc.getJsonObject("message", responseObj);
                    JSONObject companyDetails = generalFunc.getJsonObject("CompanyDetails", message_obj);
                    String Restaurant_Cuisine = generalFunc.getJsonValueStr("Restaurant_Cuisine", companyDetails);

                    restcusineNameTxt.setText(Restaurant_Cuisine);
                    setOpeningHrData(generalFunc.getJsonValueStr("vMonToSlot", companyDetails), generalFunc.retrieveLangLBl("", "LBL_MONDAY_TXT"));
                    setOpeningHrData(generalFunc.getJsonValueStr("vTueToSlot", companyDetails), generalFunc.retrieveLangLBl("", "LBL_TUESDAY_TXT"));
                    setOpeningHrData(generalFunc.getJsonValueStr("vWedToSlot", companyDetails), generalFunc.retrieveLangLBl("", "LBL_WEDNESDAY_TXT"));
                    setOpeningHrData(generalFunc.getJsonValueStr("vThuToSlot", companyDetails), generalFunc.retrieveLangLBl("", "LBL_THURSDAY_TXT"));
                    setOpeningHrData(generalFunc.getJsonValueStr("vFriToSlot", companyDetails), generalFunc.retrieveLangLBl("", "LBL_FRIDAY_TXT"));
                    setOpeningHrData(generalFunc.getJsonValueStr("vSatToSlot", companyDetails), generalFunc.retrieveLangLBl("", "LBL_SATURDAY_TXT"));
                    setOpeningHrData(generalFunc.getJsonValueStr("vSunToSlot", companyDetails), generalFunc.retrieveLangLBl("", "LBL_SUNDAY_TXT"));
                    setData(message_obj);

                    String eNonVegToggleDisplay = generalFunc.getJsonValueStr("eNonVegToggleDisplay", message_obj);
                    if (eNonVegToggleDisplay.equalsIgnoreCase("Yes")) {
                        VegNovegFilterArea.setVisibility(View.VISIBLE);
                        foodTypetxt.setText(LBL_VEG_ONLY);
                    }

                    String eFavStore = generalFunc.getJsonValueStr("eFavStore", message_obj);
                    if (eFavStore.equalsIgnoreCase("Yes")) {
                        likeButton.setLiked(true);
                    } else {
                        likeButton.setLiked(false);
                    }


                    new AsyncTask<String, String, String>() {
                        @Override
                        protected void onPreExecute() {
//                            findViewById(R.id.restaurantViewFloatingBtn).setVisibility(View.GONE);
                            super.onPreExecute();
                        }

                        @Override
                        protected String doInBackground(String... strings) {

                            setAdapterData(companyDetails);
                            return null;
                        }

                        @Override
                        protected void onPostExecute(String s) {
                            super.onPostExecute(s);
                            mProgressBar.setVisibility(View.GONE);
                            if (!generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
                                main_layout.setVisibility(View.VISIBLE);
                            }
                            backCardArea.setVisibility(View.VISIBLE);
                            menuArea.setVisibility(View.VISIBLE);
//                            findViewById(R.id.restaurantViewFloatingBtn).setVisibility(View.VISIBLE);
                            addMenuViewdata();
//                            Logger.d("TIME_STAMP","9 >>"+System.currentTimeMillis());
                            Logger.d("TIME_STAMP", "totalSize >>" + totalSize);

                        }
                    }.execute("");

                    if (generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {

                        JSONArray arr = generalFunc.getJsonArray("BANNER_DATA", responseObj);

                        if (arr != null && arr.length() > 0) {
                            ArrayList<String> imagesList = new ArrayList<String>();
                            mCardAdapter = new CardPagerAdapter();

                            int height = MyApp.getInstance().getCurrentAct().getResources().getDimensionPixelSize(R.dimen._160sdp);
                            int margin = MyApp.getInstance().getCurrentAct().getResources().getDimensionPixelSize(R.dimen._5sdp);
                            int width_ = margin * 2;
                            int width = Utils.getWidthOfBanner(MyApp.getInstance().getCurrentAct(), width_);

                            for (int i = 0; i < arr.length(); i++) {
                                JSONObject obj_temp = generalFunc.getJsonObject(arr, i);
                                String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                                String imageURL = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), vImage, width, height);
                                imagesList.add(imageURL);
                                mCardAdapter.addCardItem(imageURL, getActContext(), width, height);

                            }

                            mCardShadowTransformer = new ShadowTransformer(bannerViewPager, mCardAdapter);

                            bannerViewPager.setAdapter(mCardAdapter);
                            bannerViewPager.setPageTransformer(false, mCardShadowTransformer);
                            bannerViewPager.setOffscreenPageLimit(3);
                            bannerArea.setVisibility(View.VISIBLE);
                            main_layout.setVisibility(View.GONE);


                        } else {
                            bannerArea.setVisibility(View.GONE);
                            main_layout.setVisibility(View.VISIBLE);
                        }
                    }


                } else {
                    //  mBiodataExapandable.notifyDataSetChanged();
                }
            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }

    private void setOpeningHrData(String slotTime, String dayName) {
        HashMap<String, String> setOpeningHrData = new HashMap<>();
        setOpeningHrData.put("DayName", dayName);
        setOpeningHrData.put("DayTime", Utils.checkText(slotTime) ? slotTime : LBL_CLOSED_TXT);
        slotsArray.add(setOpeningHrData);
    }

    private void setAdapterData(JSONObject companyDetailsObj) {

        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                companyDetails = generalFunc.getJsonObject("CompanyDetails", companyDetailsObj);
                restaurant_Arr = generalFunc.getJsonArray("CompanyFoodData", companyDetailsObj);
                Recomendation_Arr = generalFunc.getJsonArray("Recomendation_Arr", companyDetailsObj);

                list.clear();
                menuList.clear();
                recommandlist.clear();

                getRecommandedArray();
                getRestaturantArray();
                setAadapter();

            }
        });

    }

    private void getRecommandedArray() {
        if (Recomendation_Arr != null && Recomendation_Arr.length() > 0) {


            int numOfColumn = getActContext() == null ? 2 : getNoOfColumns;
            int heightOfImage = (int) (((Utils.getScreenPixelWidth(getActContext()) / numOfColumn) - Utils.dipToPixels(getActContext(), 16)) / 1.77777778);
            int width = ((int) Utils.getScreenPixelWidth(getActContext()) / numOfColumn) - Utils.dipToPixels(getActContext(), 16);


            HashMap<String, String> recommandHeaderObj = new HashMap<>();
            recommandHeaderObj.put("Type", "HEADER");
            recommandHeaderObj.put("menuName", LBL_RECOMMENDED);
            recommandHeaderObj.put("vMenuItemCount", Recomendation_Arr.length() + "");
            recommandHeaderObj.put("iFoodMenuId", generalFunc.getJsonValue("iFoodMenuId", "1"));
            recommandHeaderObj.put("LBL_ADD", LBL_ADD);
            // list.add(recommandHeaderObj);

            for (int j = 0; j < Recomendation_Arr.length(); j++) {
                JSONObject category_obj = generalFunc.getJsonObject(Recomendation_Arr, j);

                HashMap<String, String> recommandMenuObj = new HashMap<>();
                recommandMenuObj.put("Type", "GRID");
                recommandMenuObj.put("fPrice", generalFunc.getJsonValueStr("fPrice", category_obj));
                recommandMenuObj.put("iDisplayOrder", generalFunc.getJsonValueStr("iDisplayOrder", category_obj));
                recommandMenuObj.put("iFoodMenuId", generalFunc.getJsonValueStr("iFoodMenuId", category_obj));
                recommandMenuObj.put("iMenuItemId", generalFunc.getJsonValueStr("iMenuItemId", category_obj));

                String vImage = generalFunc.getJsonValueStr("vImage", category_obj);
                recommandMenuObj.put("vImage", Utils.checkText(vImage) ? vImage : "https");
                recommandMenuObj.put("vImageResized", Utils.getResizeImgURL(getActContext(), vImage, 0, heightOfImage, width));
                recommandMenuObj.put("heightOfImage", "" + heightOfImage);

//                            recommandMenuObj.put("vImage", generalFunc.getJsonValueStr("vImage", category_obj));
                recommandMenuObj.put("vItemType", generalFunc.getJsonValueStr("vItemType", category_obj));
                recommandMenuObj.put("vItemDesc", generalFunc.getJsonValueStr("vItemDesc", category_obj));
                String fOfferAmt = generalFunc.getJsonValueStr("fOfferAmt", category_obj);
                recommandMenuObj.put("fOfferAmt", fOfferAmt);
                recommandMenuObj.put("fOfferAmtNotZero", generalFunc.parseDoubleValue(0, fOfferAmt) > 0 ? "Yes" : "No");

                String StrikeoutPrice = generalFunc.getJsonValueStr("StrikeoutPrice", category_obj);
                recommandMenuObj.put("StrikeoutPrice", StrikeoutPrice);
                recommandMenuObj.put("StrikeoutPriceConverted", generalFunc.convertNumberWithRTL(StrikeoutPrice));

                recommandMenuObj.put("fDiscountPrice", generalFunc.getJsonValueStr("fDiscountPrice", category_obj));
                recommandMenuObj.put("eFoodType", generalFunc.getJsonValueStr("eFoodType", category_obj));

                String fDiscountPricewithsymbol = generalFunc.getJsonValueStr("fDiscountPricewithsymbol", category_obj);
                recommandMenuObj.put("fDiscountPricewithsymbol", fDiscountPricewithsymbol);
                recommandMenuObj.put("fDiscountPricewithsymbolConverted", generalFunc.convertNumberWithRTL(generalFunc.convertNumberWithRTL(fDiscountPricewithsymbol)));

                recommandMenuObj.put("currencySymbol", generalFunc.getJsonValueStr("currencySymbol", category_obj));
                recommandMenuObj.put("MenuItemOptionToppingArr", generalFunc.getJsonValueStr("MenuItemOptionToppingArr", category_obj));
                recommandMenuObj.put("vCategoryName", generalFunc.getJsonValueStr("vCategoryName", category_obj));

                String vHighlightName = generalFunc.getJsonValueStr("vHighlightName", category_obj);
                recommandMenuObj.put("vHighlightName", generalFunc.retrieveLangLBl("", vHighlightName));

                recommandMenuObj.put("prescription_required", generalFunc.getJsonValueStr("prescription_required", category_obj));
                recommandMenuObj.put("LBL_ADD", LBL_ADD);
                recommandMenuObj.put("MenuItemMedia", generalFunc.getJsonValueStr("MenuItemMedia", category_obj));

                recommandlist.add(recommandMenuObj);
                reCommTxt.setVisibility(View.VISIBLE);
                if (j % 25 == 0) {
                    setAadapter();
                }
            }
        }

    }

    private void getRestaturantArray() {
        if (restaurant_Arr != null && restaurant_Arr.length() > 0) {
            int width = (int) getActContext().getResources().getDimension(R.dimen._225sdp);
            int heightOfImage = (int) getActContext().getResources().getDimension(R.dimen._95sdp);

            for (int i = 0; i < restaurant_Arr.length(); i++) {
                JSONObject headerObj = generalFunc.getJsonObject(restaurant_Arr, i);
                JSONArray categoryListObj = generalFunc.getJsonArray("menu_items", headerObj);
                HashMap<String, String> HeaderObj = new HashMap<>();
                HeaderObj.put("Type", "HEADER");
                HeaderObj.put("menuName", generalFunc.getJsonValueStr("vMenu", headerObj));
                HeaderObj.put("vMenuItemCount", generalFunc.getJsonValueStr("vMenuItemCount", headerObj));
                HeaderObj.put("iFoodMenuId", generalFunc.getJsonValueStr("iFoodMenuId", headerObj));
                HeaderObj.put("LBL_ADD", LBL_ADD);

                list.add(HeaderObj);
                list_v.add(generalFunc.getJsonValueStr("vMenu", headerObj));

                if (categoryListObj != null) {
                    for (int j = 0; j < categoryListObj.length(); j++) {
                        JSONObject category_obj = generalFunc.getJsonObject(categoryListObj, j);

                        HashMap<String, String> MenuObj = new HashMap<>();


                        MenuObj.put("fPrice", generalFunc.getJsonValueStr("fPrice", category_obj));
                        MenuObj.put("iDisplayOrder", generalFunc.getJsonValueStr("iDisplayOrder", category_obj));
                        MenuObj.put("iFoodMenuId", generalFunc.getJsonValueStr("iFoodMenuId", category_obj));
                        MenuObj.put("iMenuItemId", generalFunc.getJsonValueStr("iMenuItemId", category_obj));
                        String vImage = generalFunc.getJsonValueStr("vImage", category_obj);
                        MenuObj.put("vImage", Utils.checkText(vImage) ? vImage : "https");
                        MenuObj.put("vImageResized", Utils.getResizeImgURL(getActContext(), vImage, 0, heightOfImage, width));

                        MenuObj.put("vItemType", generalFunc.getJsonValueStr("vItemType", category_obj));
                        MenuObj.put("vItemDesc", generalFunc.getJsonValueStr("vItemDesc", category_obj));
                        String fOfferAmt = generalFunc.getJsonValueStr("fOfferAmt", category_obj);
                        MenuObj.put("fOfferAmt", fOfferAmt);
                        MenuObj.put("fOfferAmtNotZero", generalFunc.parseDoubleValue(0, fOfferAmt) > 0 ? "Yes" : "No");

                        String StrikeoutPrice = generalFunc.getJsonValueStr("StrikeoutPrice", category_obj);
                        MenuObj.put("StrikeoutPrice", StrikeoutPrice);
                        MenuObj.put("StrikeoutPriceConverted", generalFunc.convertNumberWithRTL(StrikeoutPrice));

                        MenuObj.put("fDiscountPrice", generalFunc.getJsonValueStr("fDiscountPrice", category_obj));
                        MenuObj.put("eFoodType", generalFunc.getJsonValueStr("eFoodType", category_obj));

                        String fDiscountPricewithsymbol = generalFunc.getJsonValueStr("fDiscountPricewithsymbol", category_obj);
                        MenuObj.put("fDiscountPricewithsymbol", fDiscountPricewithsymbol);
                        MenuObj.put("fDiscountPricewithsymbolConverted", generalFunc.convertNumberWithRTL(generalFunc.convertNumberWithRTL(fDiscountPricewithsymbol)));

                        MenuObj.put("currencySymbol", generalFunc.getJsonValueStr("currencySymbol", category_obj));
                        MenuObj.put("MenuItemOptionToppingArr", generalFunc.getJsonValueStr("MenuItemOptionToppingArr", category_obj));

                        String vHighlightName = generalFunc.getJsonValueStr("vHighlightName", category_obj);
                        MenuObj.put("vHighlightName", generalFunc.retrieveLangLBl("", vHighlightName));


                        MenuObj.put("prescription_required", generalFunc.getJsonValueStr("prescription_required", category_obj));
                        MenuObj.put("LBL_ADD", LBL_ADD);

                        MenuObj.put("isLastLine", "Yes");

                        if (j == categoryListObj.length() - 1 && i != restaurant_Arr.length() - 1) {
                            MenuObj.put("isLastLine", "No");
                        }

                        MenuObj.put("Type", "LIST");
                        MenuObj.put("isExpand", "No");
                        MenuObj.put("MenuItemMedia", generalFunc.getJsonValueStr("MenuItemMedia", category_obj));
                        //  MenuObj.put("Type", "GRID");

//                                int numOfColumn = getActContext() == null ? 2 : getNumOfColumns();
//                                int heightOfImage = (int) (((Utils.getScreenPixelWidth(getActContext()) / numOfColumn) - Utils.dipToPixels(getActContext(), 16)) / 1.77777778);
//                                int width = ((int) Utils.getScreenPixelWidth(getActContext()) / numOfColumn) - Utils.dipToPixels(getActContext(), 16);
//                                MenuObj.put("vImage", Utils.getResizeImgURL(getActContext(), generalFunc.getJsonValueStr("vImage", category_obj), width, heightOfImage));
//                                MenuObj.put("heightOfImage", "" + heightOfImage);

//                                int numOfColumn = getActContext() == null ? 2 : getNumOfColumns();
//                                Logger.d("numOfColumn", "::" + numOfColumn);
//                                int heightOfImage = (int) (((Utils.getScreenPixelWidth(getActContext()) / numOfColumn) - Utils.dipToPixels(getActContext(), 16)) / 1.77777778);
//                                int width = ((int) Utils.getScreenPixelWidth(getActContext()) / numOfColumn) - Utils.dipToPixels(getActContext(), 16);
//                                MenuObj.put("vImage", Utils.getResizeImgURL(getActContext(), generalFunc.getJsonValueStr("vImage", category_obj), width, heightOfImage));
//                                MenuObj.put("heightOfImage", "" + heightOfImage);


                        list.add(MenuObj);
                        list_v.add(generalFunc.getJsonValueStr("vItemDesc", category_obj));
                        if (j % 25 == 0 && recommandlist.size() == 0) {
                            setAadapter();
                        }
                    }


                }
            }
        }
    }

    public void addMenuViewdata() {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                if (list != null && list.size() > 0) {
                    int pos = 0;
                    MenuSelectPos = 0;
                    for (int i = 0; i < list.size(); i++) {


                        if (list.get(i).get("Type").equals("HEADER")) {


                            HashMap<String, String> dataMap = new HashMap<>();
                            dataMap.put("name", list.get(i).get("menuName"));
                            dataMap.put("iFoodMenuId", list.get(i).get("iFoodMenuId"));
                            if (pos == 0) {

                                dataMap.put("isSelect", "Yes");
                            } else {
                                dataMap.put("isSelect", "No");
                            }

                            if (getIntent().hasExtra("iFoodMenuId") && getIntent().getStringExtra("iFoodMenuId").equalsIgnoreCase(list.get(i).get("iFoodMenuId"))) {
                                dataMap.put("isSelect", "Yes");
                                foodMenuIdPos = pos;
                            }
                            dataMap.put("pos", i + "");
                            pos++;
                            menuList.add(dataMap);
                            list_h.add(list.get(i).get("menuName"));
                        }
                    }
                    if (menuList.size() > 1) {
                        menuArea.setVisibility(View.VISIBLE);
                        if (foodMenuIdPos > -1) {
                            new Handler(Looper.getMainLooper()).postDelayed(() -> {
                                LinearLayoutManager layoutManager = ((LinearLayoutManager) resMenuRecyclerview.getLayoutManager());
                                if (layoutManager != null) {
                                    layoutManager.scrollToPositionWithOffset(GeneralFunctions.parseIntegerValue(0, menuList.get(foodMenuIdPos).get("pos")), 0);
                                    topClick = true;
                                    scrollMenu(foodMenuIdPos);
                                }
                                appbar.setExpanded(false);
                                foodMenuIdPos = -1;
                            }, 555);
                        }
                    } else {
                        menuArea.setVisibility(View.GONE);
                    }
                } else {
                    menuArea.setVisibility(View.GONE);
                }

            }
        });

    }

    @SuppressLint("NotifyDataSetChanged")
    private void setAadapter() {
        if (restaurantRecomMenuAdapter == null) {
            restaurantRecomMenuAdapter = new RestaurantRecomMenuAdapter(getActContext(), generalFunc, recommandlist, this);
            resRecomMenuRecyclerview.setAdapter(restaurantRecomMenuAdapter);
        } else {
            restaurantRecomMenuAdapter.notifyDataSetChanged();
        }

        if (restaurantmenuAdapter == null) {
            restaurantmenuAdapter = new RestaurantmenuAdapter(getActContext(), list, generalFunc, this);
            resMenuRecyclerview.setAdapter(restaurantmenuAdapter);
            resMenuRecyclerview.setHasFixedSize(true);
        } else {
            restaurantmenuAdapter.notifyDataSetChanged();
        }

        if (menuAdapter == null) {
            menuAdapter = new MenuAdapter(menuList, (v, position) -> {
                LinearLayoutManager layoutManager = ((LinearLayoutManager) resMenuRecyclerview.getLayoutManager());
                if (layoutManager != null) {
                    layoutManager.scrollToPositionWithOffset(GeneralFunctions.parseIntegerValue(0, menuList.get(position).get("pos")), 0);
                    topClick = true;
                    scrollMenu(position);
                }
                appbar.setExpanded(false);
            });
            menuRecyclerview.setAdapter(menuAdapter);

        } else {
            menuAdapter.notifyDataSetChanged();
        }
    }

    public void setData(JSONObject message) {
        vCompany = generalFunc.getJsonValueStr("vCompany", message);
        iMaxItemQty = generalFunc.getJsonValueStr("iMaxItemQty", message);
        fMinOrderValue = generalFunc.getJsonValueStr("fMinOrderValue", message);

        String vAvgRating = generalFunc.getJsonValueStr("vAvgRating", message);
        if (vAvgRating != null && !vAvgRating.equalsIgnoreCase("") && !vAvgRating.equalsIgnoreCase("0")) {
            ratingCntTxt.setText(generalFunc.convertNumberWithRTL(vAvgRating));
        } else {
            ratingCntTxt.setText(generalFunc.convertNumberWithRTL("0.0"));
        }

        String fPricePerPerson = generalFunc.getJsonValueStr("fPricePerPerson", message);
        if (fPricePerPerson != null && !fPricePerPerson.equalsIgnoreCase("")) {
            minOrderValTxt.setText(generalFunc.convertNumberWithRTL(fPricePerPerson));
        } else {
            minOrderValTxt.setText(generalFunc.convertNumberWithRTL("0"));
            minOrderArea.setVisibility(View.GONE);
            minView.setVisibility(View.GONE);

        }

        restNametxt.setText(vCompany);
        titleTxtView.setText(vCompany);
        titleTxtView.setAllCaps(false);

        String Restaurant_OrderPrepareTime = generalFunc.getJsonValueStr("Restaurant_OrderPrepareTime", message);
        if (Restaurant_OrderPrepareTime != null && !Restaurant_OrderPrepareTime.equalsIgnoreCase("")) {
            deliveryValTxt.setText(generalFunc.convertNumberWithRTL(Restaurant_OrderPrepareTime));
        }

        titleDailogTxt.setText(vCompany);
        addressDailogTxt.setText(generalFunc.getJsonValueStr("vCaddress", message));

        if (generalFunc.retrieveValue("ENABLE_TIMESLOT_ADDON").equalsIgnoreCase("Yes")) {
            oldTimeSlotsArea.setVisibility(View.GONE);
            newTimeSlotsArea.setVisibility(View.VISIBLE);

            if (openingHrArea.getChildCount() > 0) {
                openingHrArea.removeAllViewsInLayout();
            }
            MyUtils.timeSlotRow(getActContext(), generalFunc, openingHrArea, slotsArray, true);
        } else {
            oldTimeSlotsArea.setVisibility(View.VISIBLE);
            newTimeSlotsArea.setVisibility(View.GONE);

            timeHTxt.setText(generalFunc.getJsonValueStr("monfritimeslot_TXT", message));
            timeSatTxt.setText(generalFunc.getJsonValueStr("satsuntimeslot_TXT", message));
            timeVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("monfritimeslot_Time", message)));
            timeVSatTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("satsuntimeslot_Time", message)));
        }


        String Restaurant_OfferMessage = generalFunc.getJsonValueStr("Restaurant_OfferMessage", message);
        if (!Restaurant_OfferMessage.equalsIgnoreCase("")) {
            offerArea.setVisibility(View.VISIBLE);
            offerMsgTxt.setText(generalFunc.convertNumberWithRTL(Restaurant_OfferMessage));
        } else {
            offerArea.setVisibility(View.GONE);
        }
        ratingdecTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("RatingCounts", message)));
    }

    private void handleToolbarTitleVisibility(float percentage) {
        if (percentage >= PERCENTAGE_TO_SHOW_TITLE_AT_TOOLBAR) {
            if (mIsTheTitleVisible) {
                startAlphaAnimation(titleTxtView, ALPHA_ANIMATIONS_DURATION, View.VISIBLE);
                startAlphaAnimation(backarrorImgView, ALPHA_ANIMATIONS_DURATION, View.VISIBLE);
                mIsTheTitleVisible = false;
            }
        } else {

            if (!mIsTheTitleVisible) {
                startAlphaAnimation(titleTxtView, ALPHA_ANIMATIONS_DURATION, View.INVISIBLE);
                mIsTheTitleVisible = true;
            }
        }
    }

    @Override
    public void onItemClickList(View v, int position) {
        HashMap<String, String> mapData = list.get(position);
        Bundle bn = new Bundle();
        HashMap<String, String> map = new HashMap<>();
        map.put("iMenuItemId", mapData.get("iMenuItemId"));
        map.put("iFoodMenuId", mapData.get("iFoodMenuId"));
        map.put("vItemType", mapData.get("vItemType"));
        map.put("vItemDesc", mapData.get("vItemDesc"));
        //map.put("fPrice", mapData.get("fPrice"));
        if (mapData.get("fPrice").equals(mapData.get("fDiscountPrice"))) {
            map.put("fPrice", mapData.get("fPrice"));
        } else {
            map.put("fPrice", mapData.get("fDiscountPrice"));
        }
        map.put("eFoodType", mapData.get("eFoodType"));
        map.put("fOfferAmt", mapData.get("fOfferAmt"));
        map.put("vImage", mapData.get("vImage"));
        map.put("iDisplayOrder", mapData.get("iDisplayOrder"));
        map.put("StrikeoutPrice", mapData.get("StrikeoutPrice"));
        map.put("fDiscountPrice", mapData.get("fDiscountPrice"));
        map.put("fDiscountPricewithsymbol", mapData.get("fDiscountPricewithsymbol"));
        map.put("MenuItemOptionToppingArr", mapData.get("MenuItemOptionToppingArr"));
        map.put("MenuItemMedia", mapData.get("MenuItemMedia"));
        map.put("currencySymbol", mapData.get("currencySymbol"));
        map.put("iCompanyId", iCompanyId);
        map.put("vCompany", vCompany);
        map.put("fMinOrderValue", fMinOrderValue);
        map.put("iMaxItemQty", iMaxItemQty);
        map.put("Restaurant_Status", getIntent().getStringExtra("Restaurant_Status"));
        map.put("ispriceshow", getIntent().getStringExtra("ispriceshow"));
        map.put("eAvailable", getIntent().getStringExtra("eAvailable"));
        map.put("timeslotavailable", getIntent().getStringExtra("timeslotavailable"));
        bn.putSerializable("data", map);
        new ActUtils(getActContext()).startActForResult(AddBasketActivity.class, bn, Utils.ADD_TO_BASKET);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == Activity.RESULT_OK) {
            generalFunc.showMessage(cartFoodImgView, generalFunc.retrieveLangLBl("", "LBL_ADD_CART_MSG"));
        }
    }

    private void openRecommendedGrids(int position) {
        recommendedDialog = new AppCompatDialog(getActContext(), android.R.style.Theme_Translucent_NoTitleBar);
        recommendedDialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        ((ViewGroup) recommendedDialog.getWindow().getDecorView()).getChildAt(0).startAnimation(AnimationUtils.loadAnimation(getActContext(), R.anim.fab_scale_up_prj));
        recommendedDialog.setContentView(R.layout.recommended_item_list);
        Window window = recommendedDialog.getWindow();
        WindowManager.LayoutParams wlp = window.getAttributes();
        wlp.gravity = Gravity.CENTER;
        wlp.flags &= ~WindowManager.LayoutParams.FLAG_BLUR_BEHIND;
        window.setAttributes(wlp);
        recommendedDialog.getWindow().setLayout(RelativeLayout.LayoutParams.MATCH_PARENT, RelativeLayout.LayoutParams.MATCH_PARENT);

        TextView titleText = recommendedDialog.findViewById(R.id.titleText);
        titleText.setText(generalFunc.retrieveLangLBl("Recommended", "LBL_RECOMMENDED"));

        ImageView closeImg = recommendedDialog.findViewById(R.id.closeImg);
        closeImg.setOnClickListener(v -> {
            if (recommendedDialog != null) {
                recommendedDialog.dismiss();
                recommendedDialog = null;
            }
        });

        RecyclerView recyclerView = recommendedDialog.findViewById(R.id.recommendedRecyclerView);
        RecommendedListAdapter adapter = new RecommendedListAdapter(recommandlist, getActContext(), generalFunc, (v, pos) -> {
            openAddToBasketAct(pos);
            if (recommendedDialog != null) {
                recommendedDialog.dismiss();
                recommendedDialog = null;
            }
        });

        runOnUiThread(() -> {
            recyclerView.setAdapter(adapter);
            recyclerView.scrollToPosition(position);
        });
        recommendedDialog.show();
    }

    @Override
    public void onRecomItemClickList(View v, int position, boolean openGrid) {
        if (openGrid) {
            openRecommendedGrids(position);
            return;
        }
        openAddToBasketAct(position);
    }

    private void openAddToBasketAct(int position) {
        HashMap<String, String> mapData = recommandlist.get(position);
        Bundle bn = new Bundle();
        HashMap<String, String> map = new HashMap<>();
        map.put("iMenuItemId", mapData.get("iMenuItemId"));
        map.put("iFoodMenuId", mapData.get("iFoodMenuId"));
        map.put("vItemType", mapData.get("vItemType"));
        map.put("vItemDesc", mapData.get("vItemDesc"));
        //map.put("fPrice", mapData.get("fPrice"));
        if (mapData.get("fPrice").equals(mapData.get("fDiscountPrice"))) {
            map.put("fPrice", mapData.get("fPrice"));
        } else {
            map.put("fPrice", mapData.get("fDiscountPrice"));
        }
        map.put("eFoodType", mapData.get("eFoodType"));
        map.put("fOfferAmt", mapData.get("fOfferAmt"));
        map.put("vImage", mapData.get("vImage"));
        map.put("iDisplayOrder", mapData.get("iDisplayOrder"));
        map.put("StrikeoutPrice", mapData.get("StrikeoutPrice"));
        map.put("fDiscountPrice", mapData.get("fDiscountPrice"));
        map.put("fDiscountPricewithsymbol", mapData.get("fDiscountPricewithsymbol"));
        map.put("MenuItemOptionToppingArr", mapData.get("MenuItemOptionToppingArr"));
        map.put("currencySymbol", mapData.get("currencySymbol"));
        map.put("iCompanyId", iCompanyId);
        map.put("vCompany", vCompany);
        map.put("fMinOrderValue", fMinOrderValue);
        map.put("iMaxItemQty", iMaxItemQty);
        map.put("Restaurant_Status", getIntent().getStringExtra("Restaurant_Status"));
        map.put("ispriceshow", getIntent().getStringExtra("ispriceshow"));
        map.put("eAvailable", getIntent().getStringExtra("eAvailable"));
        map.put("timeslotavailable", getIntent().getStringExtra("timeslotavailable"));
        map.put("MenuItemMedia", mapData.get("MenuItemMedia"));
        bn.putSerializable("data", map);
        new ActUtils(getActContext()).startActForResult(AddBasketActivity.class, bn, Utils.ADD_TO_BASKET);
    }

    public void scrollMenu(int position) {
        if (MenuSelectPos != -1 && MenuSelectPos != position) {
            HashMap<String, String> oldData = menuList.get(MenuSelectPos);
            oldData.put("isSelect", "No");
            menuList.set(MenuSelectPos, oldData);
        }
        MenuSelectPos = position;
        HashMap<String, String> data = menuList.get(position);
        data.put("isSelect", "Yes");
        menuList.set(position, data);

        menuAdapter.notifyDataSetChanged();
        ((LinearLayoutManager) menuRecyclerview.getLayoutManager()).scrollToPositionWithOffset(position, (screenWidth / 2 - Utils.dipToPixels(getActContext(), 65)));
        //((LinearLayoutManager) menuRecyclerview.getLayoutManager()).scrollToPosition(position);
    }

    public void scrollMenu(String menuname) {
        int position = 0;
        int menuListSIze = menuList.size();
        for (int i = 0; i < menuListSIze; i++) {
            HashMap<String, String> datamap = menuList.get(i);
            if (datamap.containsValue(menuname)) {
                position = i;
                break;
            }
        }
        scrollMenu(position);
    }

    @Override
    public void onRefresh() {
        swipeRefreshLayout.setRefreshing(false);
        getRestaurantDetails(isVeg, false);

    }

    public void onClick(View v) {
        int i = v.getId();

        if (i == R.id.cartFoodImgView) {
//                new ActUtils(getActContext()).startAct(EditCartActivity.class);
            bottomCartView.performClick();
        } else if (i == R.id.backArrowImgView) {
            onBackPressed();
        } else if (i == R.id.bottomCartView) {
            String COMPANY_MINIMUM_ORDER = generalFunc.retrieveValue(Utils.COMPANY_MINIMUM_ORDER);
            if (COMPANY_MINIMUM_ORDER != null && !COMPANY_MINIMUM_ORDER.equalsIgnoreCase("0")) {
                double minimumAmt = GeneralFunctions.parseDoubleValue(0, COMPANY_MINIMUM_ORDER);

                if (finalTotal < minimumAmt) {

                    generalFunc.showMessage(backarrorImgView, LBL_MINIMUM_ORDER_NOTE + " " + generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(minimumAmt)), CurrencySymbol, true));
                    return;
                }
            }

            if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
                checkPrescription();
            } else {
                ischeckPrescriptionClick = false;
                Bundle bn = new Bundle();
                bn.putBoolean("isFromEditCard", false);
                new ActUtils(getActContext()).startActWithData(CheckOutActivity.class, bn);
            }
            //checkPrescription();
        } else if (i == R.id.rightFoodImgView) {
            Bundle bn = new Bundle();
            bn.putString("iCompanyId", iCompanyId);
            bn.putString("vCompany", vCompany);
            bn.putString("Restaurant_Status", getIntent().getStringExtra("Restaurant_Status"));
            bn.putString("ispriceshow", getIntent().getStringExtra("ispriceshow"));
            bn.putString("CheckNonVegFoodType", isSearchVeg);
            new ActUtils(getActContext()).startActWithData(SearchFoodActivity.class, bn);
        } else if (i == R.id.infoImage) {
            if (dialogsLayout.getVisibility() == View.GONE) {
                dialogsLayout.setVisibility(View.VISIBLE);
                dialogsLayoutArea.setVisibility(View.VISIBLE);
                informationDesignCardView.setVisibility(View.VISIBLE);
                ratingDesignCardView.setVisibility(View.GONE);
            } else {
                dialogsLayout.setVisibility(View.GONE);
                dialogsLayoutArea.setVisibility(View.GONE);
            }
        } else if (i == closeBtn.getId()) {
            if (dialogsLayout.getVisibility() == View.VISIBLE) {
                dialogsLayout.setVisibility(View.GONE);
                dialogsLayoutArea.setVisibility(View.GONE);
            }
        } else if (i == R.id.menubackView) {
            fabMenuLayout.setVisibility(View.GONE);
        }
    }

    public void checkPrescription() {
        if (ischeckPrescriptionClick) {
            return;
        }
        ischeckPrescriptionClick = true;
        ArrayList<String> menulist = new ArrayList<>();
        if (cartList != null && cartList.size() > 0) {
            int cartListSize = cartList.size();
            for (int i = 0; i < cartListSize; i++) {
                menulist.add(cartList.get(i).getiMenuItemId());
            }

        }
        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "CheckPrescriptionRequired");
        parameters.put("iMenuItemId", android.text.TextUtils.join(",", menulist));
        parameters.put("eSystem", Utils.eSystem_Type);
        boolean isValues = false;

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {


                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString) == true) {
                    ischeckPrescriptionClick = false;
                    Bundle bn = new Bundle();
                    bn.putBoolean("isFromEditCard", false);
                    new ActUtils(getActContext()).startActWithData(PrescriptionActivity.class, bn);

                } else {
                    ischeckPrescriptionClick = false;
                    Bundle bn = new Bundle();
                    bn.putBoolean("isFromEditCard", false);
                    new ActUtils(getActContext()).startActWithData(CheckOutActivity.class, bn);

                }
            } else {
                ischeckPrescriptionClick = false;
                Bundle bn = new Bundle();
                bn.putBoolean("isFromEditCard", false);
                new ActUtils(getActContext()).startActWithData(CheckOutActivity.class, bn);
            }
        });


    }

    @Override
    public void onBackPressed() {
        if (recommendedDialog != null) {
            recommendedDialog.dismiss();
            recommendedDialog = null;
        }

        if (fabMenuLayout.getVisibility() == View.VISIBLE) {
            fabMenuLayout.setVisibility(View.GONE);
            return;
        }

        if (dialogsLayout.getVisibility() == View.VISIBLE) {
            closeBtn.performClick();
            return;
        } else {
            if (isFavChange) {
                Bundle bn = new Bundle();
                bn.putBoolean("isFavChange", isFavChange);
                (new ActUtils(getActContext())).setOkResult(bn);
                finish();
                return;
            }
            RestaurantAllDetailsNewActivity.super.onBackPressed();
        }
    }


    private void enableSafety(String Restaurant_Safety_URL, String Restaurant_Safety_Icon) {

        safetylayout.setVisibility(View.VISIBLE);
        resSsafetydetailTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SAFETY_NOTE_DETAIL_TEXT"));
        resSsafetymeasureTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SAFETY_MEASURE"));


        if (Restaurant_Safety_Status.equalsIgnoreCase("No") && (ENABLE_STORE_WISE_BANNER != null && ENABLE_STORE_WISE_BANNER.equalsIgnoreCase("Yes"))) {

            safetyImage.setImageResource(R.drawable.ic_gallery);
            resSsafetydetailTxt.setText(generalFunc.retrieveLangLBl("View Photos", "LBL_VIEW_PHOTOS"));
            safetyImage.setColorFilter(Color.parseColor("#515151"), android.graphics.PorterDuff.Mode.SRC_IN);

        } else {
            int grid = getResources().getDimensionPixelSize(R.dimen.txt_size_20);
            String imageURL = Utils.getResizeImgURL(getActContext(), Restaurant_Safety_Icon, grid, grid);
            new LoadImage.builder(LoadImage.bind(imageURL), safetyImage).setErrorImagePath(R.drawable.ic_safety).setPlaceholderImagePath(R.drawable.ic_safety).build();


        }

        String url = Restaurant_Safety_URL;
        safetylayout.setOnClickListener(v -> {
            Intent intent = new Intent(this, SafetyDialog.class);
            intent.putExtra("URL", url);
            new ActUtils(this).startAct(intent);
            ((Activity) this).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
        });
    }
}