package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemBiddingLayoutBinding;
import com.utils.ServiceColor;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 09-07-2016.
 */
public class AllBiddingRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    private final GeneralFunctions generalFunc;
    private OnItemClickListener mItemClickListener;
    private final ArrayList<HashMap<String, String>> list;
    private FooterViewHolder footerHolder;
    private boolean isFooterEnabled;
    Context mContext;

    public AllBiddingRecycleAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false));
        } else {
            return new ViewHolder(ItemBiddingLayoutBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull final RecyclerView.ViewHolder holder, final int position) {

        if (holder instanceof final ViewHolder viewHolder) {
            final HashMap<String, String> item = list.get(position);

            viewHolder.binding.cancelBiddingTxt.setText(item.get("LBL_CANCEL_BIDDING"));
            viewHolder.binding.viewDetailsTxt.setText(item.get("LBL_VIEW_DETAILS"));
            viewHolder.binding.viewBiddingTxt.setText(item.get("LBL_VIEW_TASK_BIDDING"));
            viewHolder.binding.historyNoHTxt.setText(item.get("LBL_TASK_TXT"));
            viewHolder.binding.historyNoVTxt.setText("#" + item.get("vBiddingPostNo"));
            /*String ConvertedTripRequestDate = item.get("ConvertedTripRequestDate");
            String ConvertedTripRequestTime = item.get("ConvertedTripRequestTime");
            if (ConvertedTripRequestDate != null) {
                viewHolder.binding.dateTxt.setText(ConvertedTripRequestDate);
                viewHolder.binding.timeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AT_TEXT") + " " + ConvertedTripRequestTime);
            }*/
            viewHolder.binding.dateTxt.setText(item.get("tDisplayDate"));
            viewHolder.binding.timeTxt.setText(item.get("tDisplayTime"));

            String tSaddress = item.get("vServiceAddress");
            viewHolder.binding.sourceAddressTxt.setText(tSaddress);
            viewHolder.binding.sourceAddressHTxt.setText(item.get("LBL_BIDDING_SERVICE_ADDRESS_TXT"));

            String vServiceTitle = item.get("vTitle");
            viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
            viewHolder.binding.SelectedTypeNameTxt.setText(vServiceTitle);
            viewHolder.binding.pickupLocArea.setPadding(0, 0, 0, 0);

            viewHolder.binding.typeArea.getBackground().setColorFilter(ServiceColor.BIDDING.color, PorterDuff.Mode.SRC_ATOP);

            String vService_TEXT_color = item.get("vService_TEXT_color");
            if (Utils.checkText(vService_TEXT_color)) {
                viewHolder.binding.SelectedTypeNameTxt.setTextColor(Color.parseColor(vService_TEXT_color));
            }

            String iActiveDisplay = item.get("bidding_status");

            if (Utils.checkText(iActiveDisplay)) {
                viewHolder.binding.statusArea.setVisibility(View.VISIBLE);
                viewHolder.binding.statusVTxt.setText(iActiveDisplay);
            } else {
                viewHolder.binding.statusArea.setVisibility(View.GONE);
            }

            if (generalFunc.isRTLmode()) {
                viewHolder.binding.statusArea.setRotation(180);
                viewHolder.binding.statusVTxt.setRotation(180);
            }
            viewHolder.binding.totalFareVTxtBidding.setText(item.get("fBiddingAmount"));
            viewHolder.binding.totalFareHTxtBidding.setText(generalFunc.retrieveLangLBl("", "LBL_Total_Fare_TXT"));
//            viewHolder.binding.statusArea.setBackgroundColor(Color.parseColor(item.get("vStatus_BG_color")));
            if (Utils.checkText(item.get("vStatus_BG_color"))) {
                viewHolder.binding.statusArea.getBackground().setColorFilter(Color.parseColor(item.get("vStatus_BG_color")), PorterDuff.Mode.SRC_ATOP);
            } else {
                viewHolder.binding.statusArea.getBackground().setColorFilter(mContext.getResources().getColor(R.color.appThemeColor_1), PorterDuff.Mode.SRC_ATOP);
            }
            if (Utils.checkText(item.get("driverFullName"))) {
                viewHolder.binding.driverDetailArea.setVisibility(View.VISIBLE);
                viewHolder.binding.providerNameTxt.setText(item.get("driverFullName"));
            }
            if (Utils.checkText(item.get("driverRating"))) {
                if (Double.parseDouble(item.get("driverRating")) > 0) {
                    viewHolder.binding.driverRating.setText(item.get("driverRating"));
                } else {
                    viewHolder.binding.ratingview.setVisibility(View.GONE);
                }
            } else {
                viewHolder.binding.ratingview.setVisibility(View.GONE);
            }
            viewHolder.binding.SelectedTypeNameTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.SelectedTypeNameTxt.setSelected(true);
            viewHolder.binding.SelectedTypeNameTxt.setSingleLine(true);

            viewHolder.binding.statusVTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.statusVTxt.setSelected(true);
            viewHolder.binding.statusVTxt.setSingleLine(true);


            viewHolder.binding.viewBiddingDetailsArea.setOnClickListener(view -> btnClicked(view, position, "ViewBidding"));
            viewHolder.binding.cancelBiddingArea.setOnClickListener(view -> btnClicked(view, position, "CancelBidding"));
            viewHolder.binding.viewDetailsArea.setOnClickListener(view -> btnClicked(view, position, "ViewDetail"));


            if (item.get("showBiddingTaskBtn").equalsIgnoreCase("Yes")) {
                viewHolder.binding.viewBiddingDetailsArea.setVisibility(View.VISIBLE);
            } else {
                viewHolder.binding.viewBiddingDetailsArea.setVisibility(View.GONE);
            }
            if (item.get("showCancelBtn").equalsIgnoreCase("Yes")) {
                viewHolder.binding.cancelBiddingArea.setVisibility(View.VISIBLE);
            } else {
                viewHolder.binding.cancelBiddingArea.setVisibility(View.GONE);
            }

            if (item.get("showDetailBtn").equalsIgnoreCase("Yes")) {
                viewHolder.binding.viewDetailsArea.setVisibility(View.VISIBLE);
            } else {
                viewHolder.binding.viewDetailsArea.setVisibility(View.GONE);
            }

        } else {
            this.footerHolder = (FooterViewHolder) holder;
        }
    }

    private void btnClicked(View view, int position, String type) {
        if (mItemClickListener != null) {
            mItemClickListener.onItemClickList(view, position, type);
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    private boolean isPositionFooter(int position) {
        return position == list.size();
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        if (isFooterEnabled) {
            return list.size() + 1;
        } else {
            return list.size();
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            isFooterEnabled = false;
            footerHolder.progressArea.setVisibility(View.GONE);
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(View v, int position, String type);

        void onViewServiceClickList(View v, int position);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemBiddingLayoutBinding binding;

        private ViewHolder(ItemBiddingLayoutBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final LinearLayout progressArea;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressArea = (LinearLayout) itemView;
        }
    }
}