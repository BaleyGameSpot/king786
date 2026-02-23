package com.act.rentItem.fragment;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;

import com.act.rentItem.RentItemNewPostActivity;
import com.dialogs.OpenBottomListView;
import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import java.util.ArrayList;
import java.util.HashMap;

public class RentItemPricingDetailsFragment extends BaseFragment {

    @Nullable
    private RentItemNewPostActivity mActivity;
    private MaterialEditText amountBox, durationBox, monthdurationBox;

    private final ArrayList<HashMap<String, String>> filterTypeList = new ArrayList<>();
    private int selFilterPosition = -1;
    private LinearLayout durationArea;
    private MTextView perHTxt;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View view = inflater.inflate(R.layout.fragment_rent_item_pricing_details, container, false);

        setLabels(view);
        setData();

        return view;
    }

    @SuppressLint({"ClickableViewAccessibility", "SetTextI18n"})
    private void setLabels(View view) {
        MTextView amountHTxt = view.findViewById(R.id.amountHTxt);
        MTextView currency_txt = view.findViewById(R.id.currency_txt);
        amountBox = view.findViewById(R.id.amountBox);

        durationArea = view.findViewById(R.id.durationArea);
        monthdurationBox = view.findViewById(R.id.monthdurationBox);
        MTextView durationHTxt = view.findViewById(R.id.durationHTxt);
        durationBox = view.findViewById(R.id.durationBox);

        perHTxt = view.findViewById(R.id.perHTxt);

        if (mActivity != null) {
            if (mActivity.eType.equalsIgnoreCase("RentEstate")) {
                monthdurationBox.setVisibility(View.VISIBLE);
                monthdurationBox.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_MONTH_TXT"));
                durationBox.setVisibility(View.GONE);
            } else {
                durationBox.setVisibility(View.VISIBLE);
                monthdurationBox.setVisibility(View.GONE);
            }
            amountHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_AMOUNT"));
            amountBox.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_ENTER_AMOUNT"));
            amountBox.setHelperTextAlwaysShown(true);
            currency_txt.setText(mActivity.generalFunc.getJsonValueStr("vCurrencyPassenger", mActivity.obj_userProfile));

            durationHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_DURATION"));
            durationBox.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_DAY_WEEK_MONTH"));

            if (mActivity.generalFunc.isRTLmode()) {
                currency_txt.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.right_radius_rtl));
            }
        }

        // removeInput
        Utils.removeInput(durationBox);
        durationBox.setOnTouchListener(new setOnTouchList());
        addToClickHandler(durationBox);
    }

    private void setData() {
        if (mActivity != null) {
            amountBox.setText(mActivity.mRentItemData.getfAmountWithoutSymbol());
            durationBox.setText(mActivity.mRentItemData.geteRentItemDuration());
        }
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RentItemNewPostActivity) {
            mActivity = (RentItemNewPostActivity) requireActivity();
            buildFilterList(mActivity.generalFunc);
        }
    }

    private void buildFilterList(GeneralFunctions generalFunc) {
        if(generalFunc == null){
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
        filterTypeList.clear();

        HashMap<String, String> filte1 = new HashMap<>();
        filte1.put("vName", generalFunc.retrieveLangLBl("", "LBL_DAY_TXT"));
        filte1.put("isShowForward", "No");
        filte1.put("dateRange", "day");
        filterTypeList.add((HashMap<String, String>) filte1.clone());
        filte1.put("vName", generalFunc.retrieveLangLBl("", "LBL_WEEK_TXT"));
        filte1.put("isShowForward", "No");
        filte1.put("dateRange", "week");
        filterTypeList.add((HashMap<String, String>) filte1.clone());
        filte1.put("vName", generalFunc.retrieveLangLBl("", "LBL_MONTH_TXT"));
        filte1.put("isShowForward", "No");
        filte1.put("dateRange", "month");
        filterTypeList.add((HashMap<String, String>) filte1.clone());
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null) {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_PRICING_STRUCTURE"));
            durationArea.setVisibility(mActivity.isEListing ? View.GONE : View.VISIBLE);
            perHTxt.setVisibility(mActivity.isEListing ? View.GONE : View.VISIBLE);
            if (mActivity.isEListing) {
                durationBox.setText("");
            }
        }
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        assert mActivity != null;
        if (i == R.id.durationBox) {

            OpenBottomListView.getInstance(requireActivity(), mActivity.generalFunc.retrieveLangLBl("", "LBL_SELECT_DURATION"), filterTypeList, OpenBottomListView.OpenDirection.CENTER, true, position -> {

                selFilterPosition = position;
                durationBox.setText(filterTypeList.get(position).get("vName"));
                perHTxt.setText("(" + mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_PER") + " " + filterTypeList.get(position).get("vName") + ")");

            }, true, "", true).show(selFilterPosition, "vName");
        }
    }

    private static class setOnTouchList implements View.OnTouchListener {

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {
            if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view.hasFocus()) {
                view.performClick();
            }
            return true;
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(amountBox) && (mActivity.isEListing || Utils.checkText(durationBox) || Utils.checkText(monthdurationBox))) {
                mActivity.mRentItemData.setfAmountWithoutSymbol(Utils.getText(amountBox));
                if (mActivity.eType.equalsIgnoreCase("RentEstate")) {
                    mActivity.mRentItemData.seteRentItemDuration(Utils.getText(monthdurationBox));
                } else {
                    mActivity.mRentItemData.seteRentItemDuration(Utils.getText(durationBox));
                }
                if (mActivity.isEListing || !mActivity.isPickupAvailabilityRemove) {
                    mActivity.createRentPost();
                } else {
                    mActivity.isAvailabilityDisplay = false;
                    mActivity.setPageNext();
                }
            } else {
                mActivity.generalFunc.showMessage(mActivity.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
            }
        }
    }
}