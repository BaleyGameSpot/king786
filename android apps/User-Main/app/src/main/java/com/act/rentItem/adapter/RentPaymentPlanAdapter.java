package com.act.rentItem.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemRentItemPaymentPlanBinding;
import com.buddyverse.main.databinding.ItemSimpleTextBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentPaymentPlanAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_SIMPLE = 1;
    private final Context mContext;
    private final ArrayList<HashMap<String, String>> list;
    private final PlanOnClickListener mPlanOnClickListener;
    private int selPlanPos = -1;
    private final int appThemeColor, grayColor, whiteColor, txtDarkColor, txtLightColor;

    public RentPaymentPlanAdapter(Context context, ArrayList<HashMap<String, String>> list, PlanOnClickListener planOnClickListener) {
        this.mContext = context;
        this.list = list;
        this.mPlanOnClickListener = planOnClickListener;

        this.appThemeColor = ContextCompat.getColor(context, R.color.appThemeColor_1);
        this.grayColor = ContextCompat.getColor(context, R.color.disable_color);
        this.whiteColor = ContextCompat.getColor(context, R.color.white);
        this.txtDarkColor = ContextCompat.getColor(context, R.color.text23Pro_Dark);
        this.txtLightColor = ContextCompat.getColor(context, R.color.text23Pro_Light);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_SIMPLE) {
            return new SimpleViewHolder(ItemSimpleTextBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ViewHolder(ItemRentItemPaymentPlanBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, @SuppressLint("RecyclerView") int position) {
        HashMap<String, String> mapData = list.get(position);

        if (holder instanceof SimpleViewHolder) {
            SimpleViewHolder sHolder = (SimpleViewHolder) holder;

            sHolder.binding.simpleTxt.setPadding((int) mContext.getResources().getDimension(R.dimen._10sdp), 0, 0, 0);
            sHolder.binding.simpleTxt.setText(mapData.get("vPlanName"));

        } else if (holder instanceof ViewHolder) {
            ViewHolder vHolder = (ViewHolder) holder;

            String fAmount = mapData.get("fAmount");
            if (Utils.checkText(fAmount)) {
                vHolder.binding.txtPrice.setText(fAmount);
                vHolder.binding.txtPrice.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.txtPrice.setVisibility(View.GONE);
            }
            vHolder.binding.txtPlanName.setText(mapData.get("vPlanName"));

            if (Utils.checkText(mapData.get("tDescription"))) {
                vHolder.binding.txtDescription.setVisibility(View.VISIBLE);
                vHolder.binding.txtDescription.setHtml(Objects.requireNonNull(mapData.get("tDescription")), 0);
            } else {
                vHolder.binding.txtDescription.setVisibility(View.GONE);
            }

            if (Utils.checkText(mapData.get("iRemainingPost"))) {
                vHolder.binding.txtRemaining.setVisibility(View.VISIBLE);
                vHolder.binding.txtRemaining.setHtml(Objects.requireNonNull(mapData.get("iRemainingPost")), 0);
            } else {
                vHolder.binding.txtRemaining.setVisibility(View.GONE);
            }

            vHolder.binding.dataArea.setOnClickListener(view -> ((View) view.getParent()).performClick());
            vHolder.binding.mainArea.setOnClickListener(view -> {
                mPlanOnClickListener.onPlanClick(position, selPlanPos, mapData);
                selPlanPos = position;
            });

            if (mapData.containsKey("isCheck")) {
                if (Objects.requireNonNull(mapData.get("isCheck")).equalsIgnoreCase("Yes")) {
                    selPlanPos = position;
                }
            }

            /////--------------------------------------------------------------------------------------
            vHolder.binding.check.setVisibility(View.GONE);
            vHolder.binding.unCheck.setVisibility(View.GONE);

            if (position == selPlanPos) {

                vHolder.binding.mainArea.setBackgroundTintList(ColorStateList.valueOf(appThemeColor));

                vHolder.binding.txtPrice.setTextColor(whiteColor);
                vHolder.binding.txtPlanName.setTextColor(whiteColor);
                vHolder.binding.check.setVisibility(View.VISIBLE);

                vHolder.binding.txtDescription.setTextColor(whiteColor);
                vHolder.binding.txtRemaining.setTextColor(whiteColor);
            } else {

                vHolder.binding.unCheck.setVisibility(View.VISIBLE);

                vHolder.binding.mainArea.setBackgroundTintList(ColorStateList.valueOf(whiteColor));

                vHolder.binding.txtPrice.setTextColor(appThemeColor);
                vHolder.binding.txtPlanName.setTextColor(txtDarkColor);

                vHolder.binding.txtDescription.setTextColor(txtLightColor);
                vHolder.binding.txtRemaining.setTextColor(txtLightColor);

                if (mapData.containsKey("isDisabled")) {
                    if (Objects.requireNonNull(mapData.get("isDisabled")).equalsIgnoreCase("Yes")) {

                        vHolder.binding.mainArea.setBackgroundTintList(ColorStateList.valueOf(grayColor));
                        vHolder.binding.mainArea.setOnClickListener(null);

                        vHolder.binding.txtPrice.setTextColor(whiteColor);
                        vHolder.binding.txtPlanName.setTextColor(whiteColor);

                        vHolder.binding.txtDescription.setTextColor(whiteColor);
                        vHolder.binding.txtRemaining.setTextColor(whiteColor);

                    }
                }
            }

            if (position == (list.size() - 1)) {
                vHolder.binding.extraView.setVisibility(View.GONE);
            } else {
                vHolder.binding.extraView.setVisibility(View.VISIBLE);
            }
        }

    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    @Override
    public int getItemViewType(int position) {
        if (list.get(position).containsKey("isSimpleText")) {
            if (Objects.requireNonNull(list.get(position).get("isSimpleText")).equalsIgnoreCase("Yes")) {
                return TYPE_SIMPLE;
            }
        }
        return 0;
    }

    public void setPositionClick(RecyclerView rvRentPaymentPlan, int clickSelPos) {
        RecyclerView.ViewHolder view = rvRentPaymentPlan.findViewHolderForAdapterPosition(clickSelPos);
        if (view == null) {
            return;
        }
        view.itemView.findViewById(R.id.mainArea).performClick();
        rvRentPaymentPlan.smoothScrollToPosition(clickSelPos);
    }

    public interface PlanOnClickListener {
        void onPlanClick(int position, int selPlanPos, HashMap<String, String> list);
    }

    protected static class SimpleViewHolder extends RecyclerView.ViewHolder {

        private final ItemSimpleTextBinding binding;

        public SimpleViewHolder(ItemSimpleTextBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemRentItemPaymentPlanBinding binding;

        public ViewHolder(ItemRentItemPaymentPlanBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}