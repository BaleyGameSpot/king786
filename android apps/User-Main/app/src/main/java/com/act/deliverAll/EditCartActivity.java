package com.act.deliverAll;

import android.content.Context;
import android.os.Build;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CheckBox;
import android.widget.HorizontalScrollView;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.RelativeLayout;
import android.widget.ScrollView;
import android.widget.TextView;

import androidx.cardview.widget.CardView;
import androidx.viewpager.widget.ViewPager;

import com.act.PrescriptionActivity;
import com.activity.ParentActivity;
import com.adapter.files.deliverAll.MultiItemOptionAddonPagerAdapter;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.realmModel.Cart;
import com.realmModel.Options;
import com.realmModel.Topping;
import com.service.handler.ApiHandler;
import com.shuhart.stepview.StepView;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;

import io.realm.Realm;
import io.realm.RealmList;
import io.realm.RealmResults;

public class EditCartActivity extends ParentActivity {

    ImageView backImgView;
    MTextView titleTxt;


    MButton btn_type2;
    int submitBtnId;
    MTextView vHotelNameTxt;
    MTextView totalHTxt;
    MTextView totalVTxt;

    ImageView restaurantImgView;

    MTextView vHotelAddressTxt;
    LinearLayout itemContainer, bottomArea;
    double finalTotal = 0;
    RealmResults<Cart> realmCartList;
    ArrayList<Cart> cartList;
    ScrollView mainScrollView;

    RealmResults<Options> realmOptionResult;
    RealmResults<Topping> realmToppingResult;

    String currencySymbol;
    ImageView nocartImage;

    MTextView totalNoteTxt;
    CardView totalarea;

    RelativeLayout nocartarea;
    MTextView nocartTitleTxt, nocartMsgTxt;

    boolean isRestaurantDataLoaded = false;

    LinearLayout cartCountarea;
    StepView step_view;
    HorizontalScrollView stepScrollview;
    int x, y = 0;

    RadioButton lastCheckedRB = null;
    String selectoptionId = "";
    private int currentStep = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_edit_cart);
        initView();
    }

    @Override
    protected void onResume() {
        super.onResume();
        getLocalData();
    }

    private void getLocalData() {
        try {
            finalTotal = 0;
            realmCartList = getCartData();
            if (realmCartList.size() > 0) {
                cartList = new ArrayList<>(realmCartList);
                nocartarea.setVisibility(View.GONE);
                totalarea.setVisibility(View.VISIBLE);
                cartCountarea.setVisibility(View.VISIBLE);
                setData();
            } else {
                bottomArea.setVisibility(View.GONE);
                mainScrollView.setVisibility(View.GONE);
                nocartarea.setVisibility(View.VISIBLE);
                totalarea.setVisibility(View.GONE);
                cartCountarea.setVisibility(View.GONE);
                deleteOptionToRealm();
                if (Utils.checkText(generalFunc.getMemberId())) {
                    removeCartCall();
                }
            }
        } catch (Exception e) {
            Logger.e("Ex", "::" + e.getMessage());
        }
    }

    private void removeCartCall() {
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "removePrescriptionImagesForCart");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
        });
    }

    private RealmResults<Options> getOptionsData(String iMenuItemId, String iFoodMenuId) {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Options.class).equalTo("iMenuItemId", iMenuItemId).equalTo("iFoodMenuId", iFoodMenuId).findAll();
    }

    private RealmResults<Topping> getToppingData(String iMenuItemId, String iFoodMenuId) {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Topping.class).equalTo("iMenuItemId", iMenuItemId).equalTo("iFoodMenuId", iFoodMenuId).findAll();
    }

    private RealmResults<Cart> getCartData() {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Cart.class).findAll();
    }

    private void setData() {
        if (itemContainer.getChildCount() > 0) {
            itemContainer.removeAllViewsInLayout();
        }
        addItemView();

        Cart data = cartList.get(0);
        if (!isRestaurantDataLoaded) {
            vHotelNameTxt.setText(data.getvCompany());
            vHotelAddressTxt.setText("");

            getRestaurantData(data.getiCompanyId());
        }
    }

    private Context getActContext() {
        return EditCartActivity.this;
    }

    private void initView() {

        titleTxt = findViewById(R.id.titleTxt);
        cartCountarea = findViewById(R.id.cartCountarea);
        backImgView = findViewById(R.id.backImgView);
        vHotelNameTxt = findViewById(R.id.vHotelNameTxt);
        vHotelAddressTxt = findViewById(R.id.vHotelAddressTxt);
        itemContainer = findViewById(R.id.itemContainer);
        nocartImage = findViewById(R.id.nocartImage);
        bottomArea = findViewById(R.id.bottomArea);
        totalVTxt = findViewById(R.id.totalVTxt);
        totalHTxt = findViewById(R.id.totalHTxt);
        mainScrollView = findViewById(R.id.mainScrollView);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        totalNoteTxt = findViewById(R.id.totalNoteTxt);
        totalarea = findViewById(R.id.totalarea);
        nocartarea = findViewById(R.id.nocartarea);
        nocartTitleTxt = findViewById(R.id.nocartTitleTxt);
        nocartMsgTxt = findViewById(R.id.nocartMsgTxt);
        restaurantImgView = findViewById(R.id.restaurantImgView);

        View shadowHeaderView = findViewById(R.id.shadowHeaderView);
        shadowHeaderView.setVisibility(View.INVISIBLE);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            mainScrollView.setOnScrollChangeListener((v, scrollX, scrollY, oldScrollX, oldScrollY) -> {
                if (!v.canScrollVertically(-1)) {
                    shadowHeaderView.setVisibility(View.INVISIBLE);
                } else {
                    shadowHeaderView.setVisibility(View.VISIBLE);
                }
            });
        }

        //submitBtnId = Utils.generateViewId();
        // btn_type2.setId(submitBtnId);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }

        btn_type2.setAllCaps(false);
        addToClickHandler(btn_type2);
        addToClickHandler(backImgView);

        setLabel();
    }

    private void getRestaurantData(String iCompanyId) {
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetRestaurantDetails");
        parameters.put("iCompanyId", iCompanyId);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("appType", Utils.app_type);
        if (generalFunc.getMemberId().equals("")) {
            parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        }
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    JSONObject msg_obj = generalFunc.getJsonObject(Utils.message_str, responseString);

                    if (msg_obj != null) {
                        isRestaurantDataLoaded = true;

                        vHotelAddressTxt.setText(generalFunc.getJsonValueStr("vCaddress", msg_obj));

                        int sWidth = getResources().getDimensionPixelSize(R.dimen._60sdp);
                        int sHeight = (int) (sWidth / 1.33333333333);

                        LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) restaurantImgView.getLayoutParams();
                        mParams.width = sWidth;
                        mParams.height = sHeight;
                        restaurantImgView.setLayoutParams(mParams);

                        String Url = generalFunc.getJsonValueStr("vImage", msg_obj);
                        if (!Utils.checkText(Url)) {
                            Url = "Temp";
                        }
                        Url = Utils.getResizeImgURL(getActContext(), Url, sWidth, sHeight);
                        new LoadImage.builder(LoadImage.bind(Url), restaurantImgView).setPlaceholderImagePath(R.color.imageBg).setErrorImagePath(R.color.imageBg).build();
                    }

                }
            }
        });
    }

    private String getOptionPrice(String id) {
        String optionPrice = "";
        Realm realm = MyApp.getRealmInstance();
        Options options = realm.where(Options.class).equalTo("iOptionId", id).findFirst();
        if (options != null) {
            return options.getfUserPrice();
        }
        return optionPrice;
    }

    private Options getOptionObject(String id) {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Options.class).equalTo("iOptionId", id).findFirst();
    }

    private Topping getToppingObject(String id) {

        Realm realm = MyApp.getRealmInstance();
        Topping topping = realm.where(Topping.class).equalTo("iOptionId", id).findFirst();
        if (topping != null) {
            return topping;
        }
        return topping;
    }

    private String getToppingPrice(String id) {
        String optionPrice = "";

        Realm realm = MyApp.getRealmInstance();
        Topping options = realm.where(Topping.class).equalTo("iOptionId", id).findFirst();

        if (options != null) {
            return options.getfUserPrice();
        }
        return optionPrice;
    }

    private Cart checksameRecordExist(int pos, String toppingId, String optionId, String iFoodMenuId, String iMenuItemId) {
        Cart cart = null;

        if (cartList.get(pos).getIsOption().equalsIgnoreCase("Yes")) {

            String[] list_topping_ids = toppingId.split(",");
            List<String> list_topping_ids_list = Arrays.asList(list_topping_ids);
            Collections.sort(list_topping_ids_list);
            Realm realm = MyApp.getRealmInstance();

            RealmResults<Cart> cartlist = realm.where(Cart.class).findAll();

            if (cartlist != null && cartlist.size() > 0)

                for (int i = 0; i < realmCartList.size(); i++) {
                    Cart indexData = realmCartList.get(i);
                    String[] topping_ids = indexData.getiToppingId().split(",");
                    List<String> topping_idsList = Arrays.asList(topping_ids);
                    Collections.sort(topping_idsList);
                    if (topping_idsList.equals(list_topping_ids_list) && indexData.getiOptionId().equalsIgnoreCase(optionId) && indexData.getiFoodMenuId().equalsIgnoreCase(iFoodMenuId) && indexData.getiMenuItemId().equalsIgnoreCase(iMenuItemId)) {
                        return realmCartList.get(i);
                    }
                }
            return cart;
        }
        return cart;
    }

    private void addItemView() {
        String LBL_CUSTOMIZE = generalFunc.retrieveLangLBl("", "LBL_CUSTOMIZE");

        for (int i = 0; i < cartList.size(); i++) {
            currencySymbol = cartList.get(i).getCurrencySymbol();
            int pos = i;
            LayoutInflater topinginflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            View editCartView = topinginflater.inflate(R.layout.item_edit_cart_row, null);
            MTextView itemNameTxtView = editCartView.findViewById(R.id.itemNameTxtView);
            MTextView itemsubNameTxtView = editCartView.findViewById(R.id.itemsubNameTxtView);
            MTextView itemPriceTxtView = editCartView.findViewById(R.id.itemPriceTxtView);
            MTextView QTYNumberTxtView = editCartView.findViewById(R.id.QTYNumberTxtView);
            View editOptionsArea = editCartView.findViewById(R.id.editOptionsArea);
            MTextView customizeTxtView = editCartView.findViewById(R.id.customizeTxtView);
            ImageView minusImgView = editCartView.findViewById(R.id.minusImgView);
            ImageView addImgView = editCartView.findViewById(R.id.addImgView);

            ImageView itemTypeImgView = editCartView.findViewById(R.id.itemTypeImgView);

            Cart itemData = cartList.get(pos);


            customizeTxtView.setText(LBL_CUSTOMIZE);
            if (itemData.getIsTooping().equalsIgnoreCase("Yes") || itemData.getIsOption().equalsIgnoreCase("Yes")) {
                editOptionsArea.setVisibility(View.VISIBLE);
            } else {
                editOptionsArea.setVisibility(View.GONE);
            }

            if (itemData.geteFoodType().equalsIgnoreCase("NonVeg")) {
                itemTypeImgView.setVisibility(View.VISIBLE);
                itemTypeImgView.setImageResource(R.drawable.nonveg);
            } else if (itemData.geteFoodType().equalsIgnoreCase("Veg")) {
                itemTypeImgView.setVisibility(View.VISIBLE);
            }

            double total = GeneralFunctions.parseDoubleValue(0, cartList.get(i).getfDiscountPrice());
            String[] selToppingarray = itemData.getiToppingId().split(",");
            double toppingPrice = 0;

            if (selToppingarray != null && selToppingarray.length > 0) {
                for (String s : selToppingarray) {
                    toppingPrice = toppingPrice + GeneralFunctions.parseDoubleValue(0, getToppingPrice(s));
                }
            }
            double optionPrice = 0;
            String itemList = "";
            String optionId = itemData.getiOptionId();
            Options options = null;
            try {
                String[] selOptionarray = optionId.split(",");
                int optionSize = selOptionarray.length;
                if (selOptionarray != null && optionSize > 0) {
                    for (String s : selOptionarray) {
                        optionPrice = optionPrice + GeneralFunctions.parseDoubleValue(0, getOptionPrice(s));
                    }
                }

                if (optionId != null && !optionId.equalsIgnoreCase("")) {
                    for (String s : selOptionarray) {
                        options = getOptionObject(s);
                        if (options != null) {
                            if (itemList.equalsIgnoreCase("")) {
                                itemList = options.getvOptionName();
                            } else {
                                itemList = itemList + "," + options.getvOptionName();
                            }
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
            String toppingId = itemData.getiToppingId();
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

            if (cartList.get(i).getIspriceshow().equalsIgnoreCase("separate") && options != null) {
                total = GeneralFunctions.parseDoubleValue(0, cartList.get(i).getQty()) * (optionPrice + toppingPrice);

            } else {
                total = GeneralFunctions.parseDoubleValue(0, cartList.get(i).getQty()) * (total + toppingPrice + optionPrice);

            }
            itemPriceTxtView.setText(generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(total) + ""), currencySymbol, true));
            finalTotal = finalTotal + total;
            itemNameTxtView.setText(cartList.get(i).getvItemType());


            if (!itemList.equalsIgnoreCase("")) {
                itemsubNameTxtView.setVisibility(View.VISIBLE);
                itemsubNameTxtView.setText(itemList);
            } else {
                itemsubNameTxtView.setVisibility(View.GONE);
            }

            QTYNumberTxtView.setText(generalFunc.convertNumberWithRTL(cartList.get(i).getQty()));

            minusImgView.setOnClickListener(v -> {

                int QUANTITY = Integer.parseInt(QTYNumberTxtView.getText().toString());
                if (QUANTITY > 1) {
                    finalTotal = finalTotal - Math.abs(QUANTITY * GeneralFunctions.parseDoubleValue(0, itemData.getfDiscountPrice()));

                    QUANTITY = QUANTITY - 1;
                    updateQty(pos, QUANTITY + "");
                    getLocalData();

                } else {
                    GenerateAlertBox generateAlertBox = new GenerateAlertBox(getActContext());
                    generateAlertBox.setCancelable(false);
                    generateAlertBox.setContentMessage(generalFunc.retrieveLangLBl("", "LBL_REMOVE_TEXT"), generalFunc.retrieveLangLBl("", "LBL_DELETE_CART_ITEM"));
                    generateAlertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
                    generateAlertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
                    generateAlertBox.setBtnClickList(btn_id -> {
                        if (btn_id == 1) {
                            try {
                                Realm realm = MyApp.getRealmInstance();
                                realm.beginTransaction();
                                Cart cart = realmCartList.get(pos);
                                if (cart != null) {
                                    cart.deleteFromRealm();
                                }
                                realm.commitTransaction();
                                generateAlertBox.closeAlertBox();
                                getLocalData();
                            } catch (Exception e) {
                                Logger.e("Exception", "::" + e.getMessage());
                                generateAlertBox.closeAlertBox();
                            }
                        } else {
                            generateAlertBox.closeAlertBox();
                        }
                    });
                    generateAlertBox.showAlertBox();

                }
            });

            editOptionsArea.setOnClickListener(v -> {
                String optionId1 = cartList.get(pos).getiOptionId();
                String topingId1 = cartList.get(pos).getiToppingId();

                if (optionId1 != null && !optionId1.equalsIgnoreCase("")) {
                    String[] selOptionarray = optionId1.split(",");
                    if (selOptionarray.length == 1) {
                        if (generalFunc.getJsonValueStr("ENABLE_MULTI_OPTIONS_ADDONS", obj_userProfile).equalsIgnoreCase("YES")) {
                            OpenMultiOptionDailog(pos, false);
                        } else {
                            OpenOptionDailog(pos, false);
                        }
                    } else if (selOptionarray.length > 1) {
                        OpenMultiOptionDailog(pos, false);
                    }
                } else if (topingId1 != null && !topingId1.equalsIgnoreCase("")) {
                    String[] selTopingarray = topingId1.split(",");
                    if (selTopingarray.length == 1) {
                        if (generalFunc.getJsonValueStr("ENABLE_MULTI_OPTIONS_ADDONS", obj_userProfile).equalsIgnoreCase("YES")) {
                            OpenMultiOptionDailog(pos, false);
                        } else {
                            OpenOptionDailog(pos, false);
                        }
                    } else if (selTopingarray.length > 1) {
                        OpenMultiOptionDailog(pos, false);
                    }
                }

            });
            addImgView.setOnClickListener(v -> {
                int QUANTITY = Integer.parseInt(QTYNumberTxtView.getText().toString());
                finalTotal = finalTotal - Math.abs(QUANTITY * GeneralFunctions.parseDoubleValue(0, itemData.getfDiscountPrice()));

                if (QUANTITY >= 1) {
                    QUANTITY = QUANTITY + 1;


                    if (itemData.getIsOption().equalsIgnoreCase("Yes") || itemData.getIsTooping().equalsIgnoreCase("Yes")) {
                        openRepeatDialog(pos, QUANTITY);
                    } else {
                        updateQty(pos, QUANTITY + "");
                        getLocalData();
                    }
                }

            });

            itemContainer.addView(editCartView);
        }

        totalVTxt.setText(generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(finalTotal) + ""), currencySymbol, true));
    }

    private void OpenMultiOptionDailog(int pos, boolean isNew) {
        selectoptionId = "";
        realmOptionResult = getOptionsData(cartList.get(pos).getiMenuItemId(), cartList.get(pos).getiFoodMenuId());
        realmToppingResult = getToppingData(cartList.get(pos).getiMenuItemId(), cartList.get(pos).getiFoodMenuId());

        String[] selToppingarray = cartList.get(pos).getiToppingId().split(",");
        selectoptionId = cartList.get(pos).getiOptionId();
        String[] selOptionarray = selectoptionId.split(",");

        ArrayList<String> mOptionIdList = new ArrayList<>(Arrays.asList(selOptionarray));
        final ArrayList<String>[] mToppingListId = new ArrayList[]{new ArrayList<>(Arrays.asList(selToppingarray))};

        final BottomSheetDialog optionDailog = new BottomSheetDialog(getActContext());

        View contentView = View.inflate(getActContext(), R.layout.dialog_cart_edit_options, null);

        optionDailog.setContentView(contentView, new ViewGroup.LayoutParams(ViewGroup.LayoutParams.FILL_PARENT, Utils.dpToPx(500, getActContext())));
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(Utils.dpToPx(600, getActContext()));
        optionDailog.setCancelable(false);
        View bottomSheetView = optionDailog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);

        bottomSheetView.findViewById(R.id.singleOptionAddonCardView).setVisibility(View.GONE);
        CardView multiItemCardView = (CardView) bottomSheetView.findViewById(R.id.multiOptionAddonCardView);
        multiItemCardView.setVisibility(View.GONE);

        ViewPager mViewPager = (ViewPager) bottomSheetView.findViewById(R.id.multiItemViewPager);

        ImageView arrowPrevious = (ImageView) bottomSheetView.findViewById(R.id.arrowPrevious);
        ImageView arrowNext = (ImageView) bottomSheetView.findViewById(R.id.arrowNext);
        if (generalFunc != null && generalFunc.isRTLmode()) {
            arrowPrevious.setRotation(0);
            arrowNext.setRotation(180);
        }

        MultiItemOptionAddonPagerAdapter mMultiItemOptionAddonPagerAdapter = new MultiItemOptionAddonPagerAdapter(bottomSheetView.getContext(), generalFunc, new MultiItemOptionAddonPagerAdapter.MultiItemOptionAddonListener() {
            @Override
            public void radioButtonPressed(String iOptionsCategoryId, ArrayList<String> mOptionIdList, List<Double> mTotalAmountList, RealmList<Options> mRealmOptionsList) {
                selectoptionId = "";
                if (mOptionIdList.size() > 0) {
                    for (int i = 0; i < mOptionIdList.size(); i++) {
                        if (selectoptionId.equals("")) {
                            selectoptionId = mOptionIdList.get(i);
                        } else {
                            selectoptionId = selectoptionId + "," + mOptionIdList.get(i);
                        }
                    }
                }
            }

            @Override
            public void checkBoxPressed(String iOptionsCategoryId, ArrayList<String> toppingListId, List<Double> mToppingPriceAmountList, RealmList<Topping> mRealmToppingList) {
                mToppingListId[0] = toppingListId;
            }
        });
        mViewPager.setAdapter(mMultiItemOptionAddonPagerAdapter);

        LinearLayout previousArea = (LinearLayout) bottomSheetView.findViewById(R.id.previousArea);
        previousArea.setVisibility(View.INVISIBLE);
        LinearLayout nextArea = (LinearLayout) bottomSheetView.findViewById(R.id.nextArea);
        step_view = bottomSheetView.findViewById(R.id.step_view);
        stepScrollview = bottomSheetView.findViewById(R.id.stepScrollview);
        nextArea.setVisibility(View.INVISIBLE);

        TextView txtPrevious = (TextView) bottomSheetView.findViewById(R.id.txtPrevious);
        TextView txtNext = (TextView) bottomSheetView.findViewById(R.id.txtNext);
        if (generalFunc != null) {
            txtPrevious.setText(generalFunc.retrieveLangLBl("Previous", "LBL_PREVIOUS"));
            txtNext.setText(generalFunc.retrieveLangLBl("Next", "LBL_NEXT"));
        }

        previousArea.setOnClickListener(v -> mViewPager.setCurrentItem(mViewPager.getCurrentItem() - 1, true));
        nextArea.setOnClickListener(v -> mViewPager.setCurrentItem(mViewPager.getCurrentItem() + 1, true));

        step_view.setOnStepClickListener(step -> {

            if (step_view.getCurrentStep() == step) {
                return;
            }

            if (generalFunc != null && generalFunc.isRTLmode()) {
                currentStep = step_view.getStepCount() - step - 1;
                step_view.go(currentStep, true);

                mViewPager.setCurrentItem(currentStep, true);
                if (step_view.getStepCount() > 5) {
                    x = (step_view.getStepCount() - currentStep) * Utils.dpToPx(70, getActContext());
                    stepScrollview.scrollTo(x, y);
                }
                return;
            }
            currentStep = step;
            step_view.go(step, true);
            mViewPager.setCurrentItem(step, true);

            if (step_view.getStepCount() > 5) {
                x = step * Utils.dpToPx(90, getActContext());
                stepScrollview.scrollTo(x, y);
            }
        });
        mViewPager.setOnTouchListener((v, event) -> true);
        mViewPager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int i, float v, int i1) {

            }

            @Override
            public void onPageSelected(int position) {
                if (position == 0) {
                    previousArea.setVisibility(View.INVISIBLE);
                } else {
                    previousArea.setVisibility(View.VISIBLE);
                }
                if (position < mViewPager.getAdapter().getCount() - 1) {
                    nextArea.setVisibility(View.VISIBLE);
                } else {
                    nextArea.setVisibility(View.INVISIBLE);
                }
            }

            @Override
            public void onPageScrollStateChanged(int i) {

            }
        });

        MTextView vItemNameTxt = bottomSheetView.findViewById(R.id.vItemNameTxt);
        vItemNameTxt.setText(cartList.get(pos).getvItemType());
        MTextView vCancelTxt = bottomSheetView.findViewById(R.id.vCancelTxt);
        MButton btn_type2 = ((MaterialRippleLayout) bottomSheetView.findViewById(R.id.btn_type2)).getChildView();

        vCancelTxt.setOnClickListener(v -> {
            optionDailog.dismiss();
            getLocalData();
        });
        if (generalFunc != null) {
            vCancelTxt.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));
            btn_type2.setText(generalFunc.retrieveLangLBl("Update Cart", "LBL_UPDATE_CART"));
        }
        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setOnClickListener(v -> {


            String toppingId = "";
            Realm realm = MyApp.getRealmInstance();
            realm.beginTransaction();
            Cart cart = cartList.get(pos);
            if (mToppingListId[0].size() > 0) {
                for (int i = 0; i < mToppingListId[0].size(); i++) {
                    if (toppingId.equals("")) {
                        toppingId = mToppingListId[0].get(i);
                    } else {
                        toppingId = toppingId + "," + mToppingListId[0].get(i);
                    }
                }
            }
            if (isNew) {
                Cart newCart = checksameRecordExist(pos, toppingId, selectoptionId, realmCartList.get(pos).getiFoodMenuId(), realmCartList.get(pos).getiMenuItemId());
                if (newCart == null) {
                    newCart = new Cart();
                    newCart.setCurrencySymbol(cart.getCurrencySymbol());
                    newCart.setiToppingId(toppingId);
                    newCart.setiOptionId(selectoptionId);
                    newCart.setIsOption(cart.getIsOption());
                    newCart.setIsTooping(cart.getIsTooping());
                    newCart.setQty("1");
                    newCart.setiCompanyId(cart.getiCompanyId());
                    newCart.setvCompany(cart.getvCompany());
                    newCart.setiFoodMenuId(cart.getiFoodMenuId());
                    newCart.setiMenuItemId(cart.getiMenuItemId());
                    newCart.seteFoodType(cart.geteFoodType());
                    newCart.setiServiceId(cart.getiServiceId());
                    newCart.setvImage(cart.getvImage());
                    newCart.setfDiscountPrice(cart.getfDiscountPrice());
                    newCart.setvItemType(cart.getvItemType());
                    newCart.setMilliseconds(Calendar.getInstance().getTimeInMillis());

                    newCart.setIspriceshow(cart.getIspriceshow());
                } else {
                    int qty = GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + 1;
                    // int qty = GeneralFunctions.parseIntegerValue(0, newCart.getQty());
                    newCart.setQty(qty + "");
                }
                realm.insertOrUpdate(newCart);
                realm.commitTransaction();

            } else {
                Cart newCart = checksameRecordExist(pos, toppingId, selectoptionId, realmCartList.get(pos).getiFoodMenuId(), realmCartList.get(pos).getiMenuItemId());
                if (newCart == null) {

                    cart.setiToppingId(toppingId);
                    cart.setiOptionId(selectoptionId);
                    realm.commitTransaction();
                } else {
                    // newCart.setQty(GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + "");
                    if (newCart.getMilliseconds() == cart.getMilliseconds()) {
                        //  newCart.setQty(GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + 1 + "");
                        newCart.setQty(GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + "");

                    } else {
                        newCart.setQty(GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + GeneralFunctions.parseIntegerValue(0, cart.getQty()) + "");
                    }

                    realm.insertOrUpdate(newCart);

                    if (GeneralFunctions.parseIntegerValue(0, newCart.getQty()) != GeneralFunctions.parseIntegerValue(0, cart.getQty())) {
                        cart.deleteFromRealm();
                    }
                    realm.commitTransaction();


                }
            }
            getLocalData();
            optionDailog.dismiss();
        });

        mMultiItemOptionAddonPagerAdapter.setSelectedData(mOptionIdList, mToppingListId[0]);

        if (generalFunc != null) {
            JSONArray multiData = generalFunc.getJsonArray(cartList.get(pos).getMultiItemJsonData());

            if (multiData != null) {
                if (multiData.length() > 0) {
                    multiItemCardView.setVisibility(View.VISIBLE);
                }
                if (multiData.length() > 1) {
                    nextArea.setVisibility(View.VISIBLE);
                }
                mMultiItemOptionAddonPagerAdapter.setCategoryArrayList(multiData, true);
                step_view.setStepsNumber(multiData.length());
                // btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_NEXT"));
                if (multiData.length() > 5) {


                    ViewGroup.LayoutParams layoutParams = step_view.getLayoutParams();
                    layoutParams.width = Utils.dpToPx(multiData.length() * 100f, getActContext());
                    step_view.setLayoutParams(layoutParams);
                }

                if (multiData.length() == 1) {
                    step_view.setVisibility(View.GONE);
                }
            }
        }
        LayoutDirection.setLayoutDirection(optionDailog);
        optionDailog.show();
    }

    private void updateQty(int pos, String qty) {
        Cart cart = realmCartList.get(pos);
        Realm realm = MyApp.getRealmInstance();
        realm.beginTransaction();
        cart.setQty(qty);
        realm.insertOrUpdate(cart);
        realm.commitTransaction();
    }

    private void setLabel() {

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EDIT_CART"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CHECKOUT"));
        totalHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOTAL_TXT"));
        totalNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EXCLUDING_TAXES_TXT"));
        nocartTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMPTY_CART_H_LBL"));
        nocartMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMPTY_CART_SUB_H_LBL"));
    }

    private void OpenOptionDailog(int pos, boolean isNew) {
        selectoptionId = "";

        realmOptionResult = getOptionsData(cartList.get(pos).getiMenuItemId(), cartList.get(pos).getiFoodMenuId());
        realmToppingResult = getToppingData(cartList.get(pos).getiMenuItemId(), cartList.get(pos).getiFoodMenuId());

        String[] selToppingarray = cartList.get(pos).getiToppingId().split(",");
        String selectOption = cartList.get(pos).getiOptionId();
        ArrayList<String> selToppingList = new ArrayList<>(Arrays.asList(selToppingarray));

        if (cartList.get(pos).getiOptionId() != null & !cartList.get(pos).getiOptionId().equalsIgnoreCase("")) {
            selectoptionId = cartList.get(pos).getiOptionId();
        }

        final BottomSheetDialog optionDailog = new BottomSheetDialog(getActContext());
        View contentView = View.inflate(getActContext(), R.layout.dialog_cart_edit_options, null);

        optionDailog.setContentView(contentView, new ViewGroup.LayoutParams(ViewGroup.LayoutParams.FILL_PARENT, Utils.dpToPx(400, getActContext())));
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(Utils.dpToPx(400, getActContext()));
        optionDailog.setCancelable(false);
        View bottomSheetView = optionDailog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);

        bottomSheetView.findViewById(R.id.singleOptionAddonCardView).setVisibility(View.VISIBLE);
        bottomSheetView.findViewById(R.id.multiOptionAddonCardView).setVisibility(View.GONE);
        LinearLayout optionArea = bottomSheetView.findViewById(R.id.optionArea);
        LinearLayout topingArea = bottomSheetView.findViewById(R.id.topingArea);
        MTextView optionTitleTxt = bottomSheetView.findViewById(R.id.optionTitleTxt);
        MTextView topingTitleTxt = bottomSheetView.findViewById(R.id.topingTitleTxt);
        LinearLayout optionContainer = bottomSheetView.findViewById(R.id.optionContainer);
        LinearLayout topingContainer = bottomSheetView.findViewById(R.id.topingContainer);
        MTextView vItemNameTxt = bottomSheetView.findViewById(R.id.vItemNameTxt);
        MTextView vCancelTxt = bottomSheetView.findViewById(R.id.vCancelTxt);

        MButton btn_type2 = ((MaterialRippleLayout) bottomSheetView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("Update Cart", "LBL_UPDATE_CART"));
        vCancelTxt.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));
        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setOnClickListener(v -> {
            String toppingId = "";
            Realm realm = MyApp.getRealmInstance();
            realm.beginTransaction();
            Cart cart = cartList.get(pos);
            if (selToppingList.size() > 0) {
                for (int i = 0; i < selToppingList.size(); i++) {
                    if (toppingId.equals("")) {
                        toppingId = selToppingList.get(i);
                    } else {
                        toppingId = toppingId + "," + selToppingList.get(i);
                    }
                }
            }

            if (isNew) {
                Cart newCart = checksameRecordExist(pos, toppingId, selectoptionId, realmCartList.get(pos).getiFoodMenuId(), realmCartList.get(pos).getiMenuItemId());
                if (newCart == null) {
                    newCart = new Cart();
                    newCart.setCurrencySymbol(cart.getCurrencySymbol());
                    newCart.setiToppingId(toppingId);
                    newCart.setiOptionId(selectoptionId);
                    newCart.setIsOption(cart.getIsOption());
                    newCart.setIsTooping(cart.getIsTooping());
                    newCart.setQty("1");
                    newCart.setiCompanyId(cart.getiCompanyId());
                    newCart.setvCompany(cart.getvCompany());
                    newCart.setiFoodMenuId(cart.getiFoodMenuId());
                    newCart.setiMenuItemId(cart.getiMenuItemId());
                    newCart.seteFoodType(cart.geteFoodType());
                    newCart.setiServiceId(cart.getiServiceId());
                    newCart.setvImage(cart.getvImage());
                    newCart.setfDiscountPrice(cart.getfDiscountPrice());
                    newCart.setvItemType(cart.getvItemType());
                    newCart.setMilliseconds(Calendar.getInstance().getTimeInMillis());

                    newCart.setIspriceshow(cart.getIspriceshow());
                } else {
                    int qty = GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + 1;
                    newCart.setQty(qty + "");
                }
                realm.insertOrUpdate(newCart);
                realm.commitTransaction();

            } else {
                Cart newCart = checksameRecordExist(pos, toppingId, selectoptionId, realmCartList.get(pos).getiFoodMenuId(), realmCartList.get(pos).getiMenuItemId());
                if (newCart == null) {

                    cart.setiToppingId(toppingId);
                    cart.setiOptionId(selectoptionId);
                    realm.commitTransaction();
                } else {
                    if (newCart.getMilliseconds() == cart.getMilliseconds()) {
                        newCart.setQty(GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + "");
                    } else {
                        newCart.setQty(GeneralFunctions.parseIntegerValue(0, newCart.getQty()) + GeneralFunctions.parseIntegerValue(0, cart.getQty()) + "");
                    }

                    realm.insertOrUpdate(newCart);

                    if (GeneralFunctions.parseIntegerValue(0, newCart.getQty()) != GeneralFunctions.parseIntegerValue(0, cart.getQty())) {
                        cart.deleteFromRealm();
                    }
                    realm.commitTransaction();
                }
            }
            getLocalData();
            optionDailog.dismiss();
        });
        vCancelTxt.setOnClickListener(v -> {
            optionDailog.dismiss();
            getLocalData();
        });

        topingTitleTxt.setText(generalFunc.retrieveLangLBl("Select Topping", "LBL_SELECT_TOPPING"));
        optionTitleTxt.setText(generalFunc.retrieveLangLBl("Select Options", "LBL_SELECT_OPTIONS"));
        vItemNameTxt.setText(cartList.get(pos).getvItemType());


        if (realmOptionResult != null && realmOptionResult.size() > 0) {
            optionArea.setVisibility(View.VISIBLE);

            for (int i = 0; i < realmOptionResult.size(); i++) {
                int optionpos = i;

                LayoutInflater optioninflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                View optionview = optioninflater.inflate(R.layout.item_option, null);
                MTextView optionName = optionview.findViewById(R.id.optionName);
                MTextView optionPrice = optionview.findViewById(R.id.optionPrice);
                RadioGroup optionRadioGroup = optionview.findViewById(R.id.optionRadioGroup);
                RadioButton optionradioBtn = optionview.findViewById(R.id.optionradioBtn);
                LinearLayout rowArea = optionview.findViewById(R.id.rowArea);

                if (selectOption.contains(realmOptionResult.get(i).getiOptionId())) {
                    optionradioBtn.setChecked(true);
                    lastCheckedRB = optionradioBtn;
                }
                rowArea.setOnClickListener(v -> optionradioBtn.setChecked(true));

                optionRadioGroup.setOnCheckedChangeListener((group, checkedId) -> {

                    if (lastCheckedRB != null) {
                        if (lastCheckedRB == optionradioBtn) {
                            return;
                        }
                    }
                    if (lastCheckedRB != null) {
                        lastCheckedRB.setChecked(false);
                    }

                    selectoptionId = realmOptionResult.get(optionpos).getiOptionId();
                    optionradioBtn.setChecked(true);
                    lastCheckedRB = optionradioBtn;
                });

                optionName.setText(realmOptionResult.get(i).getvOptionName());
                optionPrice.setText(generalFunc.convertNumberWithRTL(realmOptionResult.get(i).getfUserPriceWithSymbol()));
                optionContainer.addView(optionview);
            }
        }
        if (realmToppingResult != null && realmToppingResult.size() > 0) {
            topingArea.setVisibility(View.VISIBLE);

            for (int i = 0; i < realmToppingResult.size(); i++) {
                LayoutInflater topinginflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                View topingView = topinginflater.inflate(R.layout.item_toping, null);
                MTextView topingTxtView = topingView.findViewById(R.id.topingTxtView);
                MTextView topingPriceTxtView = topingView.findViewById(R.id.topingPriceTxtView);
                CheckBox topingCheckBox = topingView.findViewById(R.id.topingCheckBox);
                topingCheckBox.setTag(realmToppingResult.get(i).getiOptionId());
                LinearLayout row_area = topingView.findViewById(R.id.row_area);

                if (selToppingList != null && selToppingList.size() > 0) {
                    if (selToppingList.contains(realmToppingResult.get(i).getiOptionId())) {
                        topingCheckBox.setChecked(true);
                    }
                }

                row_area.setOnClickListener(v -> topingCheckBox.setChecked(!topingCheckBox.isChecked()));

                topingCheckBox.setOnCheckedChangeListener((buttonView, isChecked) -> {
                    if (isChecked) {
                        selToppingList.add(topingCheckBox.getTag() + "");
                    } else {
                        if (selToppingList.size() > 0) {
                            if (selToppingList.contains(topingCheckBox.getTag() + "")) {
                                selToppingList.remove(topingCheckBox.getTag() + "");
                            }
                        }
                    }
                });

                topingTxtView.setText(realmToppingResult.get(i).getvOptionName());
                topingPriceTxtView.setText(generalFunc.convertNumberWithRTL(realmToppingResult.get(i).getfUserPriceWithSymbol()));
                topingContainer.addView(topingView);
            }
        }
        LayoutDirection.setLayoutDirection(optionDailog);
        optionDailog.show();
    }

    private void deleteOptionToRealm() {
        Realm realm = MyApp.getRealmInstance();
        realm.beginTransaction();
        realm.delete(Options.class);
        realm.commitTransaction();

        Realm realmTopping = MyApp.getRealmInstance();
        realmTopping.beginTransaction();
        realmTopping.delete(Topping.class);
        realmTopping.commitTransaction();
    }

    private void openRepeatDialog(int pos, int qty) {
        Cart cartObj = cartList.get(pos);

        final BottomSheetDialog repeatCartDailog = new BottomSheetDialog(getActContext());
        View contentView = View.inflate(getActContext(), R.layout.dialog_cart_repeat, null);

        repeatCartDailog.setContentView(contentView, new ViewGroup.LayoutParams(ViewGroup.LayoutParams.FILL_PARENT, Utils.dpToPx(250, getActContext())));
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(Utils.dpToPx(250, getActContext()));
        repeatCartDailog.setCancelable(false);
        View bottomSheetView = repeatCartDailog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);

        ImageView vegImageView = bottomSheetView.findViewById(R.id.vegImageView);
        ImageView nonvegImageView = bottomSheetView.findViewById(R.id.nonvegImageView);
        MTextView vItemNameTxt = bottomSheetView.findViewById(R.id.vItemNameTxt);
        MTextView price = bottomSheetView.findViewById(R.id.price);
        MTextView offerPrice = bottomSheetView.findViewById(R.id.offerPrice);
        MTextView repeatTitleTxt = bottomSheetView.findViewById(R.id.repeatTitleTxt);
        MTextView repeatItemTxt = bottomSheetView.findViewById(R.id.repeatItemTxt);
        MTextView newItemBtn = bottomSheetView.findViewById(R.id.newItemBtn);
        MTextView repeatItemBtn = bottomSheetView.findViewById(R.id.repeatItemBtn);
        MTextView vCancelTxt = bottomSheetView.findViewById(R.id.vCancelTxt);
        vCancelTxt.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));

        vCancelTxt.setOnClickListener(v -> repeatCartDailog.dismiss());
        String itemList = "";
        String optionId = cartList.get(pos).getiOptionId();
        if (optionId != null && !optionId.equalsIgnoreCase("")) {
            String[] selOptionarray = optionId.split(",");
            for (String s : selOptionarray) {
                Options options = getOptionObject(s);
                if (options != null) {
                    if (itemList.equalsIgnoreCase("")) {
                        itemList = options.getvOptionName();
                    } else {
                        itemList = itemList + "," + options.getvOptionName();
                    }
                }
            }
        }

        if (cartList.get(pos).getiToppingId() != null && !cartList.get(pos).getiToppingId().equalsIgnoreCase("")) {
            String[] selToppingarray = cartList.get(pos).getiToppingId().split(",");

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

        repeatItemTxt.setText(itemList);

        if (cartObj.geteFoodType().equalsIgnoreCase("NonVeg")) {
            nonvegImageView.setVisibility(View.VISIBLE);
        } else if (cartObj.geteFoodType().equalsIgnoreCase("Veg")) {
            vegImageView.setVisibility(View.VISIBLE);
        }
        vItemNameTxt.setText(cartObj.getvItemType());
        price.setText(cartObj.getCurrencySymbol() + cartObj.getfDiscountPrice());
        repeatTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PREVIOUS_CUST_TITLE"));
        newItemBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE"));
        repeatItemBtn.setText(generalFunc.retrieveLangLBl("", "LBL_REPEAT"));

        newItemBtn.setOnClickListener(v -> {
            repeatCartDailog.dismiss();
            String optionId1 = cartList.get(pos).getiOptionId();
            if (optionId1 != null && !optionId1.equalsIgnoreCase("")) {
                String[] selOptionarray = optionId1.split(",");
                if (selOptionarray.length == 1) {
                    if (generalFunc.getJsonValueStr("ENABLE_MULTI_OPTIONS_ADDONS", obj_userProfile).equalsIgnoreCase("YES")) {
                        OpenMultiOptionDailog(pos, false);
                    } else {
                        OpenOptionDailog(pos, false);
                    }
                } else if (selOptionarray.length > 1) {
                    OpenMultiOptionDailog(pos, false);
                }
            }
        });
        repeatItemBtn.setOnClickListener(v -> {
            updateQty(pos, qty + "");
            getLocalData();
            repeatCartDailog.dismiss();
        });
        repeatCartDailog.show();
    }

    @Override
    public void onBackPressed() {
        if (getIntent().getBooleanExtra("isRestart", false)) {
            if (generalFunc != null) {
                if (Utils.checkText(generalFunc.getMemberId())) {
                    MyApp.getInstance().restartWithGetDataApp();
                    generalFunc.storeData(Utils.iServiceId_KEY, "");
                } else {
                    generalFunc.restartApp();
                }
            }
        } else {
            super.onBackPressed();
        }
    }

    private void checkPrescription() {
        ArrayList<String> menulist = new ArrayList<>();
        if (cartList != null && cartList.size() > 0) {
            for (int i = 0; i < cartList.size(); i++) {
                menulist.add(cartList.get(i).getiMenuItemId());
            }
        }
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CheckPrescriptionRequired");
        parameters.put("iMenuItemId", android.text.TextUtils.join(",", menulist));
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (realmCartList != null && realmCartList.size() > 0) {
                String iServiceId = realmCartList.get(0).getiServiceId();

                DeliverAllServiceCategory.getInstance().getLanguageLabelServiceWise(iServiceId, (allData, dataDic) -> {

                    HashMap<String, String> storeData = new HashMap<>();
                    storeData.put(Utils.languageLabelsKey, dataDic);
                    storeData.put(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", allData));
                    storeData.put(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", allData));
                    storeData.put(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", allData));
                    generalFunc.storeData(storeData);
                    GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
                    Utils.setAppLocal(MyApp.getInstance().getCurrentAct());

                    if (responseString != null && !responseString.equals("")) {

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                            Bundle bn = new Bundle();
                            bn.putBoolean("isFromEditCard", true);
                            bn.putString("iServiceId", iServiceId);
                            new ActUtils(getActContext()).startActWithData(PrescriptionActivity.class, bn);
                        } else {
                            Bundle bn = new Bundle();
                            bn.putBoolean("isFromEditCard", true);
                            new ActUtils(getActContext()).startActWithData(CheckOutActivity.class, bn);
                        }
                    } else {
                        Bundle bn = new Bundle();
                        bn.putBoolean("isFromEditCard", true);
                        new ActUtils(getActContext()).startActWithData(CheckOutActivity.class, bn);
                    }
                });


            }
        });
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == btn_type2.getId()) {

            if (generalFunc.retrieveValue(Utils.COMPANY_MINIMUM_ORDER) != null && !generalFunc.retrieveValue(Utils.COMPANY_MINIMUM_ORDER).equalsIgnoreCase("0")) {
                double minimumAmt = GeneralFunctions.parseDoubleValue(0, generalFunc.retrieveValue(Utils.COMPANY_MINIMUM_ORDER));

                if (finalTotal < minimumAmt) {
                    generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("", "LBL_MINIMUM_ORDER_NOTE") + " " + generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(minimumAmt)), currencySymbol, true));
                    return;
                }
            }

            if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
                checkPrescription();
            } else {
                Bundle bn = new Bundle();
                bn.putBoolean("isFromEditCard", true);
                new ActUtils(getActContext()).startActWithData(CheckOutActivity.class, bn);
            }
        }
    }
}