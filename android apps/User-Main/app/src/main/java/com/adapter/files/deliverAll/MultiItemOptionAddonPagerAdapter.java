package com.adapter.files.deliverAll;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CheckBox;
import android.widget.LinearLayout;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.viewpager.widget.PagerAdapter;

import com.general.files.EnhancedWrapContentViewPager;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.realmModel.Options;
import com.realmModel.Topping;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import io.realm.RealmList;

public class MultiItemOptionAddonPagerAdapter extends PagerAdapter {

    private final Context mContext;
    private final LayoutInflater mLayoutInflater;
    private final GeneralFunctions generalFunc;
    private final MultiItemOptionAddonListener multiItemOptionAddonListener;
    private JSONArray mCategoryArray = new JSONArray();

    ///
    RealmList<Options> realmOptionsList = new RealmList<>();
    RealmList<Topping> realmToppingList = new RealmList<>();

    private final List<Double> mOptionPriceList = new ArrayList<>();
    private ArrayList<String> mOptionIdList = new ArrayList<>();

    private final List<Double> mToppingPriceList = new ArrayList<>();
    private ArrayList<String> mToppingListId = new ArrayList<>();
    private boolean mIsEdit = false;
    private int mCurrentPosition = -1;

    public MultiItemOptionAddonPagerAdapter(Context context, GeneralFunctions generalFunc, MultiItemOptionAddonListener multiItemOptionAddonListener) {

        this.mContext = context;
        this.mLayoutInflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        this.generalFunc = generalFunc;
        this.multiItemOptionAddonListener = multiItemOptionAddonListener;
    }


    @Override
    public int getCount() {
        return mCategoryArray.length();
    }

    @Override
    public boolean isViewFromObject(@NonNull View view, @NonNull Object object) {
        return view == ((LinearLayout) object);
    }

    @Override
    public int getItemPosition(@NonNull Object object) {
        return POSITION_NONE;
    }

    @SuppressLint("SetTextI18n")
    @NonNull
    @Override
    public Object instantiateItem(@NonNull ViewGroup container, final int position) {

        View itemView = mLayoutInflater.inflate(R.layout.item_basket_option_addon, container, false);

        JSONObject categoryObject = generalFunc.getJsonObject(mCategoryArray, position);

        TextView txtCategoryTitle = (TextView) itemView.findViewById(R.id.txtCategoryTitle);
        View dividerView = (View) itemView.findViewById(R.id.divider_top_View);
        if (mCategoryArray.length() > 1) {
            dividerView.setVisibility(View.VISIBLE);
        } else {
            dividerView.setVisibility(View.GONE);

        }
        String categoryName = generalFunc.getJsonValueStr("vCatName", categoryObject);
        txtCategoryTitle.setText("" + categoryName);

        LinearLayout optionArea = (LinearLayout) itemView.findViewById(R.id.optionArea);
        TextView optionTitleTxt = (TextView) itemView.findViewById(R.id.optionTitleTxt);
        LinearLayout optionContainer = (LinearLayout) itemView.findViewById(R.id.optionContainer);

        LinearLayout topingArea = (LinearLayout) itemView.findViewById(R.id.topingArea);

        TextView topingTitleTxt = (TextView) itemView.findViewById(R.id.topingTitleTxt);
        LinearLayout topingContainer = (LinearLayout) itemView.findViewById(R.id.topingContainer);
        String categoryTitleName = generalFunc.getJsonValueStr("tOptionTitle", categoryObject);
        String topingTitleName = generalFunc.getJsonValueStr("tAddonTitle", categoryObject);

        if (Utils.checkText(categoryTitleName)) {
            optionTitleTxt.setText(categoryTitleName);
        } else {
            optionTitleTxt.setVisibility(View.GONE);
        }
        if (Utils.checkText(topingTitleName)) {
            topingTitleTxt.setText(topingTitleName);
        } else {
            topingTitleTxt.setVisibility(View.GONE);
        }

        JSONArray optionArray = generalFunc.getJsonArray("options", categoryObject);
        JSONArray addonArray = generalFunc.getJsonArray("addon", categoryObject);

        final RadioButton[] lastCheckedRB = {null};
        ArrayList<HashMap<String, String>> optionList = new ArrayList<>();
        ArrayList<HashMap<String, String>> topingList = new ArrayList<>();

        if (optionArray != null && optionArray.length() > 0) {
            for (int i = 0; i < optionArray.length(); i++) {
                int pos = i;
                JSONObject optionObject = generalFunc.getJsonObject(optionArray, i);
                HashMap<String, String> optionMap = new HashMap<>();
                LayoutInflater optioninflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                View optionview = optioninflater.inflate(R.layout.item_basket_option, null);
                MTextView optionName = optionview.findViewById(R.id.optionName);
                MTextView optionPrice = optionview.findViewById(R.id.optionPrice);
                RadioGroup optionRadioGroup = optionview.findViewById(R.id.optionRadioGroup);
                RadioButton optionradioBtn = optionview.findViewById(R.id.optionradioBtn);
                LinearLayout rowArea = optionview.findViewById(R.id.rowArea);
                optionradioBtn.setTag(pos);
                optionRadioGroup.setTag(pos);

                if (mOptionIdList != null && mOptionIdList.size() > 0 && mOptionIdList.get(position).equalsIgnoreCase(generalFunc.getJsonValueStr("iOptionId", optionObject))) {
                    optionradioBtn.setChecked(true);
                    lastCheckedRB[0] = optionradioBtn;
                }
                rowArea.setOnClickListener(v -> optionradioBtn.setChecked(true));

                optionRadioGroup.setOnCheckedChangeListener((group, checkedId) -> {
                    if (lastCheckedRB[0] != null) {
                        if (lastCheckedRB[0] == optionradioBtn) {
                            return;
                        }
                    }
                    if (lastCheckedRB[0] != null) {
                        lastCheckedRB[0].setChecked(false);
                    }
                    optionradioBtn.setChecked(true);
                    lastCheckedRB[0] = optionradioBtn;

                    mOptionIdList.set(position, optionList.get(pos).get("iOptionId"));

                    mOptionPriceList.set(position, GeneralFunctions.parseDoubleValue(0, optionList.get(pos).get("fUserPrice")));
                    multiItemOptionAddonListener.radioButtonPressed(generalFunc.getJsonValueStr("iOptionsCategoryId", categoryObject), mOptionIdList, mOptionPriceList, realmOptionsList);

                });


                optionMap.put("iOptionId", generalFunc.getJsonValueStr("iOptionId", optionObject));
                optionMap.put("vOptionName", generalFunc.getJsonValueStr("vOptionName", optionObject));
                optionMap.put("fPrice", generalFunc.getJsonValueStr("fPrice", optionObject));
                optionMap.put("eOptionType", generalFunc.getJsonValueStr("eOptionType", optionObject));
                optionMap.put("fUserPrice", generalFunc.getJsonValueStr("fUserPrice", optionObject));
                optionMap.put("fUserPriceWithSymbol", generalFunc.getJsonValueStr("fUserPriceWithSymbol", optionObject));
                optionName.setText(generalFunc.getJsonValueStr("vOptionName", optionObject));
                optionPrice.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("fUserPriceWithSymbol", optionObject)));

                optionMap.put("eDefault", generalFunc.getJsonValueStr("eDefault", optionObject));
                optionList.add(optionMap);

                if (generalFunc.getJsonValueStr("eDefault", optionObject) != null && generalFunc.getJsonValueStr("eDefault", optionObject).equalsIgnoreCase("Yes")) {
                    if (mOptionIdList != null && mOptionIdList.size() > 0 && mOptionIdList.get(position).equalsIgnoreCase("0")) {
                        optionradioBtn.setChecked(true);
                        lastCheckedRB[0] = optionradioBtn;
                        mOptionIdList.set(position, generalFunc.getJsonValueStr("iOptionId", optionObject));
                    }
                } else {
                    if (mOptionIdList != null && mOptionIdList.size() > 0 && mOptionIdList.get(position).equalsIgnoreCase("0")) {
                        mOptionIdList.set(position, "-1");
                    }
                }

                Options optionsObj = new Options();
                optionsObj.setfPrice(generalFunc.getJsonValue("fPrice", optionObject.toString()));
                optionsObj.setfUserPrice(generalFunc.getJsonValue("fUserPrice", optionObject.toString()));
                optionsObj.setfUserPriceWithSymbol(generalFunc.getJsonValue("fUserPriceWithSymbol", optionObject.toString()));
                optionsObj.setiFoodMenuId(generalFunc.getJsonValue("iFoodMenuId", optionObject.toString()));
                optionsObj.setiMenuItemId(generalFunc.getJsonValue("iMenuItemId", optionObject.toString()));
                optionsObj.setvOptionName(generalFunc.getJsonValue("vOptionName", optionObject.toString()));
                optionsObj.setiOptionId(generalFunc.getJsonValue("iOptionId", optionObject.toString()));
                optionsObj.seteDefault(generalFunc.getJsonValue("eDefault", optionObject.toString()));
                realmOptionsList.add(optionsObj);

                optionContainer.addView(optionview);
                optionArea.setVisibility(View.VISIBLE);
            }
        } else {
            optionTitleTxt.setVisibility(View.GONE);
            if (mOptionIdList != null && mOptionIdList.size() > 0 && mOptionIdList.get(position).equalsIgnoreCase("0")) {
                mOptionIdList.set(position, "-1");
            }
        }

        // Addon
        if (addonArray != null && addonArray.length() > 0) {
            for (int ai = 0; ai < addonArray.length(); ai++) {
                int pos = ai;
                JSONObject topingObject = generalFunc.getJsonObject(addonArray, ai);
                HashMap<String, String> topingMap = new HashMap<>();
                LayoutInflater topinginflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                View topingView = topinginflater.inflate(R.layout.item_basket_toping, null);
                MTextView topingTxtView = topingView.findViewById(R.id.topingTxtView);
                MTextView topingPriceTxtView = topingView.findViewById(R.id.topingPriceTxtView);
                CheckBox topingCheckBox = topingView.findViewById(R.id.topingCheckBox);
                LinearLayout row_area = topingView.findViewById(R.id.row_area);


                topingMap.put("iOptionId", generalFunc.getJsonValue("iOptionId", topingObject.toString()));
                topingMap.put("vOptionName", generalFunc.getJsonValue("vOptionName", topingObject.toString()));
                topingMap.put("fPrice", generalFunc.getJsonValue("fPrice", topingObject.toString()));
                topingMap.put("eOptionType", generalFunc.getJsonValue("eOptionType", topingObject.toString()));
                topingMap.put("fUserPrice", generalFunc.getJsonValue("fUserPrice", topingObject.toString()));
                topingMap.put("fUserPriceWithSymbol", generalFunc.getJsonValue("fUserPriceWithSymbol", topingObject.toString()));
                topingTxtView.setText(generalFunc.getJsonValue("vOptionName", topingObject.toString()));
                topingPriceTxtView.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("fUserPriceWithSymbol", topingObject.toString())));
                topingList.add(topingMap);
                topingContainer.addView(topingView);

                for (String s : mToppingListId) {
                    if (s.equalsIgnoreCase(topingList.get(pos).get("iOptionId"))) {
                        topingCheckBox.setChecked(true);
                        if (mIsEdit) {
                            mToppingPriceList.set(position, mToppingPriceList.get(position) + GeneralFunctions.parseDoubleValue(0, topingList.get(pos).get("fUserPrice")));
                        }
                    }
                }

                row_area.setOnClickListener(v -> topingCheckBox.setChecked(!topingCheckBox.isChecked()));

                topingCheckBox.setOnCheckedChangeListener((buttonView, isChecked) -> {
                    if (isChecked) {
                        mToppingListId.add(topingList.get(pos).get("iOptionId"));
                        mToppingPriceList.set(position, mToppingPriceList.get(position) + GeneralFunctions.parseDoubleValue(0, topingList.get(pos).get("fUserPrice")));
                    } else {
                        if (mToppingListId.size() > 0) {
                            if (mToppingListId.contains(topingList.get(pos).get("iOptionId"))) {
                                mToppingListId.remove(topingList.get(pos).get("iOptionId"));
                            }
                        }
                        mToppingPriceList.set(position, mToppingPriceList.get(position) - GeneralFunctions.parseDoubleValue(0, topingList.get(pos).get("fUserPrice")));
                    }
                    multiItemOptionAddonListener.checkBoxPressed(generalFunc.getJsonValueStr("iOptionsCategoryId", categoryObject), mToppingListId, mToppingPriceList, realmToppingList);
                });

                Topping topppingObj = new Topping();
                topppingObj.setfPrice(generalFunc.getJsonValue("fPrice", topingObject.toString()));
                topppingObj.setfUserPrice(generalFunc.getJsonValue("fUserPrice", topingObject.toString()));
                topppingObj.setfUserPriceWithSymbol(generalFunc.getJsonValue("fUserPriceWithSymbol", topingObject.toString()));
                topppingObj.setiFoodMenuId(generalFunc.getJsonValue("iFoodMenuId", topingObject.toString()));
                topppingObj.setiMenuItemId(generalFunc.getJsonValue("iMenuItemId", topingObject.toString()));
                topppingObj.setvOptionName(generalFunc.getJsonValue("vOptionName", topingObject.toString()));
                topppingObj.setiOptionId(generalFunc.getJsonValue("iOptionId", topingObject.toString()));
                realmToppingList.add(topppingObj);

                topingArea.setVisibility(View.VISIBLE);
            }
        } else {
            topingTitleTxt.setVisibility(View.GONE);
        }

        container.addView(itemView);
        return itemView;
    }

    @Override
    public void setPrimaryItem(@NotNull ViewGroup container, int position, @NotNull Object object) {
        super.setPrimaryItem(container, position, object);
        if (position != mCurrentPosition) {
            EnhancedWrapContentViewPager pager = (EnhancedWrapContentViewPager) container;
            mCurrentPosition = position;
            pager.measureCurrentView((LinearLayout) object);
        }
    }

    @Override
    public void destroyItem(ViewGroup container, int position, @NonNull Object object) {
        container.removeView((LinearLayout) object);
    }

    public void setCategoryArrayList(JSONArray categoryArray, boolean isEdit) {
        this.mIsEdit = isEdit;
        this.mCategoryArray = categoryArray;
        if (isEdit) {
            for (int i = 0; i < mCategoryArray.length(); i++) {
                mOptionPriceList.add(i, 0.0);
                mToppingPriceList.add(i, 0.0);

                try {
                    if (mCategoryArray.getJSONObject(i).has("options")) {
                        if (mCategoryArray.getJSONObject(i).getJSONArray("options").length() == 0) {
                            if (!mOptionIdList.get(i).equalsIgnoreCase("-1")) {
                                mOptionIdList.add(i, "0");
                            }
                        }
                    }
                } catch (JSONException e) {
                    throw new RuntimeException(e);
                }
            }
        } else {
            for (int i = 0; i < mCategoryArray.length(); i++) {
                mOptionPriceList.add(i, 0.0);
                mOptionIdList.add(i, "0");
                mToppingPriceList.add(i, 0.0);
            }
        }
        notifyDataSetChanged();
    }


    public String getCategoryArray() {
        return mCategoryArray.toString();
    }

    public void setSelectedData(ArrayList<String> optionIdList, ArrayList<String> toppingListId) {
        this.mOptionIdList = optionIdList;
        this.mToppingListId = toppingListId;
    }

    public interface MultiItemOptionAddonListener {
        void radioButtonPressed(String iOptionsCategoryId, ArrayList<String> mOptionIdList, List<Double> mTotalAmountList, RealmList<Options> realmOptionsList);

        void checkBoxPressed(String iOptionsCategoryId, ArrayList<String> mToppingListId, List<Double> mToppingPriceAmountList, RealmList<Topping> realmToppingList);
    }
}