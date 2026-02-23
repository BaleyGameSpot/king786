package com.adapter.files.deliverAll;

import android.content.Context;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.general.call.CommunicationManager;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.TrackOrderItemDesignBinding;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class TrackOrderAdapter extends RecyclerView.Adapter<TrackOrderAdapter.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> listData;
    private final ReviewItemClickListener ClickListener;

    private final int blackColor, bgColor, statusColor;
    int size2, size44, size36, size28, padding4dp;

    public TrackOrderAdapter(Context mContext, GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> listData, ReviewItemClickListener itemClickListener) {
        this.listData = listData;
        this.generalFunc = generalFunc;
        this.ClickListener = itemClickListener;
        this.blackColor = Color.parseColor("#000000");
        this.bgColor = Color.parseColor("#f8f8f8");
        this.statusColor = Color.parseColor("#5a5a5a");
        this.size2 = Utils.dipToPixels(mContext, 2);
        this.size44 = Utils.dipToPixels(mContext, 44);
        this.size36 = Utils.dipToPixels(mContext, 36);
        this.size28 = Utils.dipToPixels(mContext, 28);
        this.padding4dp = Utils.dipToPixels(mContext, 4);
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(TrackOrderItemDesignBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(ViewHolder holder, int position) {

        HashMap<String, String> mapData = listData.get(position);

        holder.binding.contentTxtView.setText(mapData.get("vStatus"));

        holder.binding.titleTxtView.setText(mapData.get("vStatusTitle"));
        if (mapData.containsKey("dDate") && Utils.checkText(mapData.get("dDate"))) {
            holder.binding.statusTimerArea.setVisibility(View.VISIBLE);
            holder.binding.timeTxtView.setText(mapData.get("dDateConverted"));
            holder.binding.amPmTxtView.setText(mapData.get("dDateAMPM"));
        } else {
            holder.binding.statusTimerArea.setVisibility(View.GONE);
            holder.binding.timeTxtView.setText("");
            holder.binding.amPmTxtView.setText("");
        }

        if (position == 0) {
            holder.binding.topView.setVisibility(View.INVISIBLE);
        } else {
            holder.binding.topView.setVisibility(View.VISIBLE);
        }

        if (position == listData.size() - 1) {
            holder.binding.bottomView.setVisibility(View.INVISIBLE);
        } else {
            holder.binding.bottomView.setVisibility(View.VISIBLE);
        }

        String iStatusCode = mapData.get("iStatusCode");


        boolean isStore = false;
        if (Utils.checkText(iStatusCode)) {
            if (iStatusCode.equalsIgnoreCase("1")) {
                holder.binding.statusImgView.setImageResource(R.drawable.track_status_order_place);
            } else if (iStatusCode.equalsIgnoreCase("2")) {
                holder.binding.statusImgView.setImageResource(R.drawable.ic_shop);
            } else if (iStatusCode.equalsIgnoreCase("4")) {
                holder.binding.statusImgView.setImageResource(R.drawable.track_status_order_store);
            } else if (iStatusCode.equalsIgnoreCase("5")) {
                holder.binding.statusImgView.setImageResource(R.drawable.track_order_on_way);
            } else if (iStatusCode.equalsIgnoreCase("8") || iStatusCode.equalsIgnoreCase("9")) {
                holder.binding.statusImgView.setImageResource(R.drawable.track_status_order_cancel);
            } else if (iStatusCode.equalsIgnoreCase("13")) {
                holder.binding.statusImgView.setImageResource(R.drawable.ic_review);
            } else if (iStatusCode.equalsIgnoreCase("14")) {
                holder.binding.statusImgView.setImageResource(R.drawable.ic_make_payment);
            } else {
                holder.binding.statusImgView.setImageResource(R.drawable.track_status_order_accept);
            }
            isStore = iStatusCode.equalsIgnoreCase("1") || iStatusCode.equalsIgnoreCase("2");
        }

        boolean finalIsStore = isStore;
        String eShowCallImg = mapData.get("eShowCallImg");
        if (eShowCallImg != null && eShowCallImg.equalsIgnoreCase("Yes")) {
            holder.binding.callView.setOnClickListener(v -> {
                //
                ClickListener.onChoose(CommunicationManager.TYPE.OTHER, finalIsStore);
            });
            holder.binding.callView.setVisibility(View.VISIBLE);
        } else {
            holder.binding.callView.setVisibility(View.GONE);
        }

        String eShowChatImg = mapData.get("eShowChatImg");
        if (eShowChatImg != null && eShowChatImg.equalsIgnoreCase("Yes")) {
            holder.binding.chatView.setVisibility(View.VISIBLE);
            holder.binding.chatView.setOnClickListener(v -> {
                //
                ClickListener.onChoose(CommunicationManager.TYPE.CHAT, finalIsStore);
            });
        } else {
            holder.binding.chatView.setVisibility(View.GONE);
        }

        if (mapData.get("eCompleted").equalsIgnoreCase("Yes")) {
            holder.binding.timeTxtView.setTextColor(holder.itemView.getResources().getColor(R.color.appThemeColor_1));
            holder.binding.amPmTxtView.setTextColor(holder.itemView.getResources().getColor(R.color.appThemeColor_1));
            holder.binding.statusImgView.setColorFilter(holder.itemView.getResources().getColor(R.color.appThemeColor_TXT_1), PorterDuff.Mode.SRC_IN);
            new CreateRoundedView(holder.itemView.getResources().getColor(R.color.appThemeColor_1), Utils.dipToPixels(holder.itemView.getContext(), mapData.get("OrderCurrentStatusCode").equalsIgnoreCase(iStatusCode) ? 22 : 18), 0, 0, holder.binding.imgContainerView);
        } else {
            holder.binding.timeTxtView.setTextColor(blackColor);
            holder.binding.statusImgView.setColorFilter(statusColor, PorterDuff.Mode.SRC_IN);
            new CreateRoundedView(bgColor, Utils.dipToPixels(holder.itemView.getContext(), mapData.get("OrderCurrentStatusCode").equalsIgnoreCase(iStatusCode) ? 22 : 18), 0, 0, holder.binding.imgContainerView);
        }

        int nwPos = position + 1;
        if (holder.binding.bottomView.getVisibility() == View.VISIBLE && (nwPos) < listData.size()) {
            HashMap<String, String> nextMapData = listData.get(nwPos);

        }

        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) holder.binding.imgContainerView.getLayoutParams();
        params.height = size36;
        params.width = size36;
        holder.binding.imgContainerView.setLayoutParams(params);
        holder.binding.timeLineContainer.setPaddingRelative(padding4dp, 0, padding4dp, 0);


        if (mapData.get("showViewBillButton").equalsIgnoreCase("Yes")) {
            holder.binding.viewDetailsArea.setVisibility(View.VISIBLE);
            holder.binding.btnViewDetails.setText(listData.get(position).get("fStoreBillAmount"));
            holder.binding.infoImg.setOnClickListener(view -> ClickListener.setOnviewBill(position));

        } else {
            holder.binding.viewDetailsArea.setVisibility(View.GONE);

        }
        if (mapData.get("showPaymentButton").equalsIgnoreCase("Yes")) {
            holder.binding.paymentArea.setVisibility(View.VISIBLE);
            holder.binding.btnPayment.setText(generalFunc.retrieveLangLBl("", "LBL_MAKE_PAYMENT"));
            holder.binding.btnPayment.setOnClickListener(view -> ClickListener.setOnMakePaymentClick(position));

        } else {
            holder.binding.paymentArea.setVisibility(View.GONE);

        }

        if (mapData.get("genieWaitingForUserApproval").equalsIgnoreCase("Yes") && mapData.get("genieUserApproved").equalsIgnoreCase("No")) {

            if (mapData.get("MenuItem").equalsIgnoreCase("")) {
                holder.binding.reviewArea.getRoot().setVisibility(View.GONE);
            } else {
                holder.binding.reviewArea.getRoot().setVisibility(View.VISIBLE);
                holder.binding.reviewArea.itemName.setText(mapData.get("iQty") + " x " + mapData.get("MenuItem"));
                new LoadImage.builder(LoadImage.bind(mapData.get("vImage")), holder.binding.reviewArea.itemImg).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
                MButton btnReview = ((MaterialRippleLayout) holder.binding.reviewArea.btnReview).getChildView();
                btnReview.setText(generalFunc.retrieveLangLBl("", "LBL_GENIE_REVIEW"));
                btnReview.setOnClickListener(view -> ClickListener.setOnreviewClick(position));
            }
        } else {
            holder.binding.reviewArea.getRoot().setVisibility(View.GONE);
        }

        holder.binding.dividerView.setVisibility(View.GONE);
    }

    @Override
    public int getItemCount() {
        return listData.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final TrackOrderItemDesignBinding binding;

        private ViewHolder(TrackOrderItemDesignBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface ReviewItemClickListener {
        void setOnreviewClick(int position);

        void setOnMakePaymentClick(int position);

        void setOnviewBill(int position);

        void onChoose(CommunicationManager.TYPE other, boolean isStore);
    }
}
