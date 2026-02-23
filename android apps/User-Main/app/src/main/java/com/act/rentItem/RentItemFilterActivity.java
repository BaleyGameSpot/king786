package com.act.rentItem;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.KmRecyclerView;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRentItemFilterBinding;
import com.buddyverse.main.databinding.ItemRentItemFilterHeaderBinding;
import com.buddyverse.main.databinding.ItemRentItemFilterServiceBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;

public class RentItemFilterActivity extends ParentActivity {

    private ActivityRentItemFilterBinding binding;
    private String eType, iCategoryId, optionId = "", subcategoryId = "";
    private final List<AppCompatCheckBox> mList = new ArrayList<>();
    private final List<String> mListSubcategory = new ArrayList<>(), mListOption = new ArrayList<>();
    private MButton resetTxt, applyTxt;

    private RentFilterAdapter mRentFilterAdapter;
    @Nullable
    private JSONArray jsonArray;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_rent_item_filter);

        iCategoryId = getIntent().getStringExtra("iCategoryId");
        eType = getIntent().getStringExtra("eType");
        subcategoryId = getIntent().getStringExtra("subcategoryId");
        optionId = getIntent().getStringExtra("optionId");

        initViews();
        getSubCategoryList();
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_FILTER"));

        //
        resetTxt = ((MaterialRippleLayout) binding.resetTxt).getChildView();
        resetTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_RESET"));
        resetTxt.setId(Utils.generateViewId());
        addToClickHandler(resetTxt);

        applyTxt = ((MaterialRippleLayout) binding.applyTxt).getChildView();
        applyTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_APPLY"));
        applyTxt.setId(Utils.generateViewId());
        addToClickHandler(applyTxt);

        //
        mRentFilterAdapter = new RentFilterAdapter();
        binding.rvRentFilter.setAdapter(mRentFilterAdapter);
    }

    private Context getActContext() {
        return RentItemFilterActivity.this;
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getSubCategoryList() {

        binding.loadingBar.setVisibility(View.VISIBLE);
        binding.contentView.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getFilterRentData");
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("iCategoryId", iCategoryId);
        parameters.put("eType", eType);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            binding.loadingBar.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    mListSubcategory.clear();
                    mListOption.clear();

                    JSONArray carList_arr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    if (carList_arr != null) {
                        binding.contentView.setVisibility(View.VISIBLE);

                        this.jsonArray = carList_arr;
                        mRentFilterAdapter.notifyDataSetChanged();
                    }

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    @SuppressLint({"SetTextI18n", "InflateParams"})
    private void categoryServices(JSONObject jsonObject, LinearLayout serviceSelectArea) {
        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull ItemRentItemFilterServiceBinding binding2 = ItemRentItemFilterServiceBinding.inflate(inflater, serviceSelectArea, false);

        binding2.filterServiceNameTxt.setText(generalFunc.getJsonValueStr("vTitle", jsonObject));
        binding2.filterServiceNameTxt.setOnClickListener(v -> binding2.chkBox.performClick());

        if (jsonObject.has("iSubCategoryId")) {
            binding2.chkBox.setId(Integer.parseInt(generalFunc.getJsonValueStr("iSubCategoryId", jsonObject)));
        } else {
            binding2.chkBox.setId(Integer.parseInt(generalFunc.getJsonValueStr("iOptionId", jsonObject)));
        }
        mList.add(binding2.chkBox);

        if (jsonObject.has("iSubCategoryId")) {
            List<String> list = Arrays.asList(subcategoryId.split(","));
            if (list.contains(generalFunc.getJsonValueStr("iSubCategoryId", jsonObject))) {
                binding2.chkBox.setChecked(true);
                binding2.chkBox.setButtonTintList(ContextCompat.getColorStateList(this, R.color.appThemeColor_1));
                mListSubcategory.add(generalFunc.getJsonValueStr("iSubCategoryId", jsonObject));
            }
        } else {
            List<String> list2 = Arrays.asList(optionId.split(","));
            if (list2.contains(generalFunc.getJsonValueStr("iOptionId", jsonObject))) {
                binding2.chkBox.setChecked(true);
                binding2.chkBox.setButtonTintList(ContextCompat.getColorStateList(this, R.color.appThemeColor_1));
                mListOption.add(generalFunc.getJsonValueStr("iOptionId", jsonObject));
            }
        }

        binding2.chkBox.setOnCheckedChangeListener((buttonView, isChecked) -> {

            if (isChecked) {
                binding2.chkBox.setButtonTintList(ContextCompat.getColorStateList(this, R.color.appThemeColor_1));
                if (jsonObject.has("iSubCategoryId")) {
                    mListSubcategory.add(String.valueOf(binding2.chkBox.getId()));
                } else {
                    mListOption.add(String.valueOf(binding2.chkBox.getId()));
                }
            } else {
                binding2.chkBox.setButtonTintList(ContextCompat.getColorStateList(this, R.color.text23Pro_Light));
                if (jsonObject.has("iSubCategoryId")) {
                    mListSubcategory.remove(String.valueOf(binding2.chkBox.getId()));
                } else {
                    mListOption.remove(String.valueOf(binding2.chkBox.getId()));
                }
            }
        });
        serviceSelectArea.addView(binding2.getRoot());
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();

        } else if (i == applyTxt.getId()) {

            Bundle bn = new Bundle();
            bn.putString("iCategoryId", iCategoryId);
            bn.putString("eType", eType);
            bn.putString("subcategoryId", TextUtils.join(",", mListSubcategory));
            bn.putString("optionId", TextUtils.join(",", mListOption));
            new ActUtils(getActContext()).setOkResult(bn);
            finish();

        } else if (i == resetTxt.getId()) {
            for (int j = 0; j < mList.size(); j++) {
                mList.get(j).setChecked(false);
                subcategoryId = "";
                optionId = "";
                mListSubcategory.clear();
                mListOption.clear();
            }
        }
    }

    private class RentFilterAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> implements KmRecyclerView.KmStickyListener {

        @NonNull
        @Override
        public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            return new ViewHolder(ItemRentItemFilterHeaderBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }

        @Override
        public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
            ViewHolder viewH = (ViewHolder) holder;

            JSONObject itemObj = generalFunc.getJsonObject(jsonArray, position);

            viewH.binding.shadowHeaderView.setVisibility(View.GONE);
            viewH.binding.titleTxt.setText(generalFunc.getJsonValueStr("headerTitle", itemObj));

            JSONArray mCategory = generalFunc.getJsonArray("subData", itemObj);
            if (mCategory != null) {
                for (int j = 0; j < mCategory.length(); j++) {
                    categoryServices(generalFunc.getJsonObject(mCategory, j), viewH.binding.serviceSelectArea);
                }
            }
        }

        @Override
        public int getItemCount() {
            return jsonArray != null ? jsonArray.length() : 0;
        }

        //
        protected static class ViewHolder extends RecyclerView.ViewHolder {

            private final ItemRentItemFilterHeaderBinding binding;

            private ViewHolder(ItemRentItemFilterHeaderBinding binding) {
                super(binding.getRoot());
                this.binding = binding;
            }
        }

        //
        @Override
        public int getHeaderPositionForItem(int itemPosition) {
            return itemPosition;
        }

        @Override
        public int getHeaderLayout(int headerPosition) {
            return R.layout.item_rent_item_filter_header;
        }

        @Override
        public void bindHeaderData(View header, int headerPosition) {
            MTextView titleTxt = header.findViewById(R.id.titleTxt);
            LinearLayout subItemView = header.findViewById(R.id.subItemView);

            JSONObject itemObj = generalFunc.getJsonObject(jsonArray, headerPosition);
            titleTxt.setText(generalFunc.getJsonValueStr("headerTitle", itemObj));
            subItemView.setVisibility(View.GONE);

            View shadowHeaderView = header.findViewById(R.id.shadowHeaderView);
            shadowHeaderView.setVisibility(View.VISIBLE);
        }

        @Override
        public boolean isHeader(int itemPosition) {
            return false;
        }
    }
}