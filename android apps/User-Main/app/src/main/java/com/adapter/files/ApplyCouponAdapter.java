package com.adapter.files;

import android.annotation.SuppressLint;
import android.text.SpannableString;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemApplyCouponBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

/**
 * Created by Admin on 03-07-18.
 */

public class ApplyCouponAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener mItemClickListener;
    private final RecyclerView mRecyclerView;
    private int currSelectedPosition = -1;
    private final String appliedCouponCode;

    public ApplyCouponAdapter(ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, String appliedCouponCode, RecyclerView mRecyclerView, OnItemClickListener mItemClickListener) {
        this.list = list;
        this.generalFunc = generalFunc;
        this.appliedCouponCode = appliedCouponCode;
        this.mRecyclerView = mRecyclerView;
        this.mItemClickListener = mItemClickListener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemApplyCouponBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    // Replace the contents of a view (invoked by the layout manager)
    @SuppressLint({"SetTextI18n", "NotifyDataSetChanged"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, @SuppressLint("RecyclerView") int position) {

        if (holder instanceof ViewHolder) {
            final HashMap<String, String> item = list.get(position);
            final ViewHolder viewHolder = (ViewHolder) holder;


            String vCouponCode = item.get("vCouponCode");
            viewHolder.binding.useCodeVTxt.setText(vCouponCode);
            viewHolder.binding.tDescriptionVTxt.setText(item.get("tDescription"));

            StringBuilder sb = new StringBuilder(Objects.requireNonNull(item.get("eType")));
            sb.setCharAt(0, Character.toUpperCase(sb.charAt(0)));
            String capitalizedEType = sb.toString();

            viewHolder.binding.tDiscountTypeVTxt.setText(item.get("LBL_PROMOCODE_DISCOUNT_TYPE_TXT") + ": " + capitalizedEType);
            viewHolder.binding.viewDetailsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_DETAILS"));

            if (vCouponCode != null && vCouponCode.equalsIgnoreCase(appliedCouponCode)) {
                if (currSelectedPosition == -1) {
                    currSelectedPosition = position;
                }
                viewHolder.binding.btnType2.setText(item.get("LBL_REMOVE_TEXT"));
            } else {
                viewHolder.binding.btnType2.setText(item.get("LBL_PROMOCODE_TAP_TO_APPLY_TXT"));
            }


            String attrString1 = item.get("LBL_USE_AND_SAVE_LBL");

            boolean isCash = Objects.requireNonNull(item.get("eType")).equalsIgnoreCase("cash");
            String currencySymbol = isCash ? " " + item.get("vSymbol") + "" : " ";
            String fDiscountSymbol = item.get("fDiscountSymbol");

            String fDiscount = Utils.checkText(fDiscountSymbol) ? fDiscountSymbol : (isCash ? generalFunc.formatNumAsPerCurrency(generalFunc, item.get("fDiscount"), currencySymbol, true) + "" : item.get("fDiscount") + "%");


//            String htmlString = "<font color=\"" + holder.itemView.getContext().getResources().getColor(R.color.black) + "\">" + attrString1 + "</font>" +
//                    "<font color=\"" + holder.itemView.getContext().getResources().getColor(R.color.appThemeColor_1) + "\">" + " " + fDiscount + "</font>";

//            SpannableString spannableString = new SpannableString(htmlString);
            viewHolder.binding.useAmountVTxt.setText(attrString1 + " " + fDiscount);

            if (Objects.requireNonNull(item.get("eFreeDelivery")).equalsIgnoreCase("Yes")) {
                viewHolder.binding.useAmountVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_USE_SAVE_DELIVERY_CHARGES"));
            }

            if (!Objects.requireNonNull(item.get("eValidityType")).equalsIgnoreCase("PERMANENT")) {
                viewHolder.binding.dExpiryDateVTxt.setVisibility(View.VISIBLE);
                SpannableString text = new SpannableString(item.get("LBL_VALID_TILL_TXT") + ": " + item.get("dExpiryDate"));
                viewHolder.binding.dExpiryDateVTxt.setText(text);
            } else {
                viewHolder.binding.dExpiryDateVTxt.setVisibility(View.GONE);
            }


            viewHolder.binding.btnType2.setOnClickListener(view -> {
                if (vCouponCode != null && vCouponCode.equalsIgnoreCase(appliedCouponCode)) {
                    if (mItemClickListener != null) {
                        mItemClickListener.onItemClickListRemoveCode(view, position, "useCode");
                    }
                } else {
                    if (mItemClickListener != null) {
                        mItemClickListener.onItemClickList(view, position);
                    }
                }
            });


            if (currSelectedPosition == -1 || currSelectedPosition != position) {
                viewHolder.binding.indicatorImg.setImageResource(R.mipmap.ic_arrow_down);
                viewHolder.binding.detailArea.setVisibility(View.GONE);
            } else {
                viewHolder.binding.indicatorImg.setImageResource(R.mipmap.ic_arrow_up);
                viewHolder.binding.detailArea.setVisibility(View.VISIBLE);
            }


            viewHolder.binding.promoArea.setOnClickListener(view -> {

                if (currSelectedPosition == position) {
                    currSelectedPosition = -1;
                    if (viewHolder.binding.detailArea.getVisibility() == View.GONE) {
                        viewHolder.binding.indicatorImg.setImageResource(R.mipmap.ic_arrow_up);
                        viewHolder.binding.detailArea.setVisibility(View.VISIBLE);
                    } else {
                        viewHolder.binding.indicatorImg.setImageResource(R.mipmap.ic_arrow_down);
                        viewHolder.binding.detailArea.setVisibility(View.GONE);
                    }
                    return;
                }
                if (viewHolder.binding.detailArea.getVisibility() == View.GONE) {
                    currSelectedPosition = position;
                    viewHolder.binding.indicatorImg.setImageResource(R.mipmap.ic_arrow_up);
                    viewHolder.binding.detailArea.setVisibility(View.VISIBLE);
                    mRecyclerView.smoothScrollToPosition(position);

                } else {
                    currSelectedPosition = -1;
                    viewHolder.binding.indicatorImg.setImageResource(R.mipmap.ic_arrow_down);
                    viewHolder.binding.detailArea.setVisibility(View.GONE);
                }

                Utils.hideKeyboard(holder.itemView.getContext());
                notifyDataSetChanged();
            });
        }
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        return list.size();
    }

    public interface OnItemClickListener {
        void onItemClickList(View v, int position);

        void onItemClickListRemoveCode(View v, int position, String string);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemApplyCouponBinding binding;

        private ViewHolder(ItemApplyCouponBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}