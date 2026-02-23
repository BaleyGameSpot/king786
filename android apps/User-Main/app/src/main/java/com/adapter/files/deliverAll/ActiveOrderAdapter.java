package com.adapter.files.deliverAll;


import android.content.Context;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.text.TextUtils;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemListOrdersBinding;
import com.utils.LoadImage;
import com.utils.ServiceColor;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 09-07-2016.
 */

public class ActiveOrderAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list;
    Context mContext;
    boolean isFooterEnabled = false;
    View footerView;
    FooterViewHolder footerHolder;
    private OnItemClickListener mItemClickListener;

    public ActiveOrderAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        if (viewType == TYPE_FOOTER) {
            View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false);
            this.footerView = v;
            return new FooterViewHolder(v);
        } else {
            return new ViewHolder(ItemListOrdersBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }

    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, final int position) {


        if (holder instanceof ViewHolder) {
            final ViewHolder viewHolder = (ViewHolder) holder;

            HashMap<String, String> item = list.get(position);

            String image_url = item.get("vImage");
            if (Utils.checkText(image_url)) {
                new LoadImage.builder(LoadImage.bind(image_url), viewHolder.binding.OrderHotelImage).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            } else {
                viewHolder.binding.OrderHotelImage.setImageResource(R.color.imageBg);
            }


            viewHolder.binding.orderHotelName.setText(item.get("vCompany"));
            if (Utils.checkText(item.get("vRestuarantLocation"))) {
                viewHolder.binding.orderHotelAddress.setText(item.get("vRestuarantLocation"));
            } else {
                viewHolder.binding.location.setVisibility(View.GONE);
            }
            /*if (item.containsKey("tOrderRequestDate")) {
                viewHolder.binding.orderDateVTxt.setText(item.get("tOrderRequestDate"));
            } else if (item.containsKey("ConvertedOrderRequestDate")) {
                viewHolder.binding.orderDateVTxt.setText(item.get("ConvertedOrderRequestDate"));
                viewHolder.binding.orderTimeVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AT_TEXT") + " " + item.get("ConvertedOrderRequestTime"));
            }*/

            viewHolder.binding.orderDateVTxt.setText(item.get("tDisplayDate"));
            viewHolder.binding.orderTimeVTxt.setText(item.get("tDisplayTime"));


            viewHolder.binding.totalVtxt.setText(item.get("fNetTotal"));
            viewHolder.binding.totalHtxt.setText(item.get("LBL_ORDER_AMOUNT_TXT"));
            viewHolder.binding.btnHelp.setText(item.get("LBL_HELP"));
            viewHolder.binding.btnRating.setText(generalFunc.retrieveLangLBl("", "LBL_RATE_ORDER"));
            viewHolder.binding.btnViewDetails.setText(item.get("LBL_VIEW_DETAILS"));
            viewHolder.binding.serviceNameTxt.setText(item.get("vServiceCategoryName"));
            viewHolder.binding.orderNoHTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_ORDER"));
            viewHolder.binding.orderNoTxtView.setText("#" + item.get("vOrderNo"));

            if (Utils.checkText(item.get("vServiceCategoryName"))) {
                viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
                viewHolder.binding.orderNoTxtView.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._13sdp));
                viewHolder.binding.orderNoTxtView.setTextColor(mContext.getResources().getColor(R.color.mspUnSelected));

                viewHolder.binding.orderNoHTxtView.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._13sdp));
                viewHolder.binding.orderNoHTxtView.setTextColor(mContext.getResources().getColor(R.color.mspUnSelected));

                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) viewHolder.binding.contentArea.getLayoutParams();
                params.setMargins(0, 8, 0, 0);
                viewHolder.binding.contentArea.setLayoutParams(params);
            } else {
                viewHolder.binding.orderNoTxtView.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._11sdp));
                viewHolder.binding.orderNoHTxtView.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._11sdp));
                viewHolder.binding.typeArea.setVisibility(View.GONE);
                viewHolder.binding.orderNoTxtView.setTextColor(Color.parseColor("#777676"));
                viewHolder.binding.orderNoHTxtView.setTextColor(Color.parseColor("#777676"));
                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) viewHolder.binding.contentArea.getLayoutParams();
                params.setMargins(0, -14, 0, 0);
                viewHolder.binding.contentArea.setLayoutParams(params);
            }

            int serviceColor = 1;

            if (Utils.checkText(item.get("iServiceId"))) {
                serviceColor = Integer.parseInt(item.get("iServiceId"));
            }

            viewHolder.binding.typeArea.getBackground().setColorFilter(Color.parseColor(ServiceColor.UI_COLORS[serviceColor % ServiceColor.UI_COLORS.length]), PorterDuff.Mode.SRC_ATOP);
            String vService_TEXT_color = ServiceColor.UI_TEXT_COLORS[0];
            if (Utils.checkText(vService_TEXT_color)) {
                viewHolder.binding.serviceNameTxt.setTextColor(Color.parseColor(vService_TEXT_color));
            }


            viewHolder.binding.serviceNameTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.serviceNameTxt.setSelected(true);
            viewHolder.binding.serviceNameTxt.setSingleLine(true);

            viewHolder.binding.orderStatus.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.orderStatus.setSelected(true);
            viewHolder.binding.orderStatus.setSingleLine(true);
            String vOrderStatus = item.get("vOrderStatus");
            if (Utils.checkText(item.get("vOrderStatus"))) {
                viewHolder.binding.statusArea.setVisibility(View.VISIBLE);
                viewHolder.binding.orderStatus.setText(vOrderStatus);
            } else {
                viewHolder.binding.statusArea.setVisibility(View.GONE);
            }

            if (item.get("DisplayLiveTrack").equalsIgnoreCase("Yes")) {
                viewHolder.binding.TrackOrderBtnArea.setVisibility(View.VISIBLE);
                viewHolder.binding.vieDetailsArea.setVisibility(View.GONE);
            } else {
                viewHolder.binding.TrackOrderBtnArea.setVisibility(View.GONE);
                viewHolder.binding.vieDetailsArea.setVisibility(View.VISIBLE);
            }

            if (item.get("isRatingButtonShow").equalsIgnoreCase("Yes")) {
                viewHolder.binding.helpArea.setVisibility(View.GONE);
                viewHolder.binding.RatingArea.setVisibility(View.VISIBLE);
            } else {
                viewHolder.binding.helpArea.setVisibility(View.VISIBLE);
                viewHolder.binding.RatingArea.setVisibility(View.GONE);
            }

            viewHolder.binding.btnTrackOrder.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(view, position, "track");
                }
            });

            viewHolder.binding.btnTrackOrder.setText(item.get("LBL_TRACK_ORDER"));

            viewHolder.binding.btnHelp.setOnClickListener(v -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(v, position, "help");
                }
            });
            viewHolder.binding.btnRating.setOnClickListener(v -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(v, position, "rating");
                }
            });
            viewHolder.binding.btnViewDetails.setOnClickListener(v -> mItemClickListener.onItemClickList(v, position, "view"));


        } else {
            FooterViewHolder footerHolder = (FooterViewHolder) holder;
            this.footerHolder = footerHolder;
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

    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.GONE);
    }

    public interface OnItemClickListener {
        void onItemClickList(View v, int position, String isSelect);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {
        private ItemListOrdersBinding binding;

        public ViewHolder(ItemListOrdersBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    class FooterViewHolder extends RecyclerView.ViewHolder {
        LinearLayout progressArea;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressArea = (LinearLayout) itemView;
        }
    }
}