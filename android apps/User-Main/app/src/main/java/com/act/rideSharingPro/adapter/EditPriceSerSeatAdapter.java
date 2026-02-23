package com.act.rideSharingPro.adapter;

import android.annotation.SuppressLint;
import android.text.Editable;
import android.text.InputFilter;
import android.text.InputType;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.databinding.ItemRideProEditPriceBinding;
import com.utils.Logger;
import com.utils.Utils;
import com.view.AutoFitEditText;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.Locale;

public class EditPriceSerSeatAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private final GeneralFunctions generalFunc;
    private final String currency;
    @Nullable
    private JSONArray mEditPriceArr;

    RideSharingProHomeActivity mActivity;
    private final String defaultAmountVal = AutoFitEditText.convertCommaToDecimal("0.00", false);

    public EditPriceSerSeatAdapter(@NonNull GeneralFunctions generalFunctions, @NonNull String currency, @NonNull JSONArray jsonArray, RideSharingProHomeActivity mActivity) {
        this.generalFunc = generalFunctions;
        this.currency = currency;
        this.mEditPriceArr = jsonArray;
        this.mActivity = mActivity;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemRideProEditPriceBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        ViewHolder bHolder = (ViewHolder) holder;

        JSONObject mItemObj = generalFunc.getJsonObject(mEditPriceArr, position);

        bHolder.binding.startAddressTxt.setText(generalFunc.getJsonValue("add", generalFunc.getJsonValueStr("startPoint", mItemObj)));
        bHolder.binding.endAddressTxt.setText(generalFunc.getJsonValue("add", generalFunc.getJsonValueStr("endPoint", mItemObj)));
        bHolder.binding.currencyTxt.setText(currency);

        bHolder.binding.priceEdit.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        bHolder.binding.priceEdit.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});
        bHolder.binding.priceEdit.setHint(defaultAmountVal);
        bHolder.binding.priceEdit.setText(generalFunc.getJsonValueStr("recommended_price", mItemObj));

        bHolder.binding.priceEdit.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                savePrice(position, mItemObj, bHolder.binding.priceEdit);
            }

            @Override
            public void afterTextChanged(Editable s) {
                bHolder.binding.priceEdit.afterTextChange(s);
            }
        });

        bHolder.binding.minusBtn.setOnClickListener(view -> {
            if (Utils.checkText(bHolder.binding.priceEdit) && GeneralFunctions.parseDoubleValue(0, bHolder.binding.priceEdit.getTxt()) >= 0.0) {
                bHolder.binding.priceEdit.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, bHolder.binding.priceEdit.getTxt()) - 1));
                if (!(GeneralFunctions.parseDoubleValue(0, bHolder.binding.priceEdit.getTxt()) >= 0.0)) {
                    bHolder.binding.priceEdit.setText(defaultAmountVal);
                }
                savePrice(position, mItemObj, bHolder.binding.priceEdit);
            }
        });
        bHolder.binding.plusBtn.setOnClickListener(view -> {
            if (Utils.checkText(bHolder.binding.priceEdit)) {
                bHolder.binding.priceEdit.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, bHolder.binding.priceEdit.getTxt()) + 1));
            } else {
                bHolder.binding.priceEdit.setText("1.00");
            }
            savePrice(position, mItemObj, bHolder.binding.priceEdit);
        });
    }

    private void savePrice(int position, JSONObject mItemObj, AutoFitEditText priceEdit) {
        try {
            mItemObj.put("recommended_price", priceEdit.getTxt());
            if (mEditPriceArr != null) {
                mEditPriceArr.put(position, mItemObj);
                if (mActivity != null) {
                    mActivity.rsPublishFragment.mPublishData.setPointRecommendedPrice(mEditPriceArr == null ? "" : mEditPriceArr.toString());
                }
            }
        } catch (JSONException e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    @Override
    public int getItemCount() {
        return mEditPriceArr != null ? mEditPriceArr.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(JSONArray servicesArr) {
        this.mEditPriceArr = servicesArr;
        notifyDataSetChanged();
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemRideProEditPriceBinding binding;

        private ViewHolder(ItemRideProEditPriceBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}