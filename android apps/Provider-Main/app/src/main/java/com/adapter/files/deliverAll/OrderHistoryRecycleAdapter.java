package com.adapter.files.deliverAll;

import android.content.Context;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.text.TextUtils;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.core.view.ViewCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ItemOrderHistoryDesignBinding;
import com.buddyverse.providers.databinding.ItemOrderHistoryHeaderDesignBinding;
import com.utils.LoadImage;
import com.utils.ServiceColor;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by admin on 21/05/18.
 */

public class OrderHistoryRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    public static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    public static final int TYPE_HEADER = 3;
    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list;
    Context mContext;
    boolean isFooterEnabled = false;
    View footerView;
    FooterViewHolder footerHolder;
    private OnItemClickListener mItemClickListener;
    int imagewidth;

    public OrderHistoryRecycleAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;
        imagewidth = (int) mContext.getResources().getDimension(R.dimen._50sdp);
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        if (viewType == TYPE_HEADER) {
            return new HeaderViewHolder(ItemOrderHistoryHeaderDesignBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else if (viewType == TYPE_FOOTER) {
            View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false);
            this.footerView = v;
            return new FooterViewHolder(v);
        } else {
            return new ViewHolder(ItemOrderHistoryDesignBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, final int position) {

        if (holder instanceof ViewHolder) {

            final HashMap<String, String> item = list.get(position);
            final ViewHolder viewHolder = (ViewHolder) holder;

            String vServiceCategoryName = item.get("vServiceCategoryName");
            viewHolder.binding.serviceNameTxt.setText(vServiceCategoryName);

            if (!vServiceCategoryName.isEmpty()) {
                viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
                viewHolder.binding.orderNoTxtView.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._13sdp));
                viewHolder.binding.orderNoTxtView.setTextColor(Color.parseColor("#141414"));
                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) viewHolder.binding.contentArea.getLayoutParams();
                params.setMargins(0, 8, 0, 0);
                viewHolder.binding.contentArea.setLayoutParams(params);

            } else {
                viewHolder.binding.orderNoTxtView.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._11sdp));
                viewHolder.binding.typeArea.setVisibility(View.GONE);
                viewHolder.binding.orderNoTxtView.setTextColor(Color.parseColor("#777676"));
                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) viewHolder.binding.contentArea.getLayoutParams();
                params.setMargins(0, -14, 0, 0);
                viewHolder.binding.contentArea.setLayoutParams(params);
            }
            viewHolder.binding.orderNoTxtView.setText("#" + item.get("vOrderNo"));
            viewHolder.binding.totalItemsTxtView.setText(item.get("TotalItems") + " " + item.get("LBL_ITEM"));
            viewHolder.binding.orderHotelAddress.setText(item.get("vUserAddress"));
            //viewHolder.binding.orderDateVTxt.setText(item.get("ConvertedOrderRequestDate"));
            //viewHolder.binding.orderTimeVTxt.setText(item.get("ConvertedOrderRequestTime"));
            viewHolder.binding.orderDateVTxt.setText(item.get("tDisplayDate"));
            viewHolder.binding.orderTimeVTxt.setText(item.get("tDisplayTimeAbbr"));
            viewHolder.binding.userNameTxtView.setText(item.get("UseName"));
            viewHolder.binding.orderHotelName.setText(item.get("vCompany"));

            String EarningFare = item.get("EarningFare");
            String iStatus = item.get("iStatus");
            String iStatusCode = item.get("iStatusCode");

            viewHolder.binding.orderPriceTxtView.setText(EarningFare);
            viewHolder.binding.orderStatusTxtView.setText(iStatus);
            int serviceColor = 0;

            if (Utils.checkText(item.get("iServiceId"))) {
                serviceColor = Integer.parseInt(item.get("iServiceId"));
            }
            viewHolder.binding.typeArea.getBackground().setColorFilter(Color.parseColor(ServiceColor.UI_COLORS[serviceColor]), PorterDuff.Mode.SRC_ATOP);
            viewHolder.binding.serviceNameTxt.setTextColor(Color.parseColor(ServiceColor.UI_TEXT_COLORS[0]));

            if (generalFunc.isRTLmode()) {
                viewHolder.binding.orderStatusArea.setRotation(180);
                viewHolder.binding.orderStatusTxtView.setRotation(180);
            }

            viewHolder.binding.orderStatusTxtView.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.orderStatusTxtView.setSelected(true);
            viewHolder.binding.orderStatusTxtView.setSingleLine(true);

            viewHolder.binding.serviceNameTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.serviceNameTxt.setSelected(true);
            viewHolder.binding.serviceNameTxt.setSingleLine(true);

            String image_url = item.get("vImage");

            if (Utils.checkText(image_url)) {

                new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, image_url, imagewidth, imagewidth)), viewHolder.binding.storeImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();


            } else {
                viewHolder.binding.storeImgView.setImageResource(R.mipmap.ic_no_pic_user);
            }

            if (iStatus.equalsIgnoreCase("Declined") || iStatusCode.equalsIgnoreCase("9")) {

                ViewCompat.setBackgroundTintList(
                        viewHolder.binding.orderStatusArea,
                        ColorStateList.valueOf(mContext.getResources().getColor(R.color.defaultTextColor)));

            } else if (iStatus.equalsIgnoreCase("Cancelled") || iStatusCode.equalsIgnoreCase("8")) {

                ViewCompat.setBackgroundTintList(
                        viewHolder.binding.orderStatusArea,
                        ColorStateList.valueOf(mContext.getResources().getColor(R.color.defaultTextColor)));
            } else if (iStatus.equalsIgnoreCase("Delivered") || iStatusCode.equalsIgnoreCase("6")) {
                ViewCompat.setBackgroundTintList(
                        viewHolder.binding.orderStatusArea,
                        ColorStateList.valueOf(mContext.getResources().getColor(R.color.green)));
            } else if (iStatus.equalsIgnoreCase("Refunds") || iStatusCode.equalsIgnoreCase("7")) {
                ViewCompat.setBackgroundTintList(
                        viewHolder.binding.orderStatusArea,
                        ColorStateList.valueOf(mContext.getResources().getColor(R.color.red)));
            } else {
                viewHolder.binding.orderStatusArea.setVisibility(View.GONE);
            }

            if (EarningFare != null && EarningFare.trim().equals("")) {
                viewHolder.binding.waitAmtGenerateArea.setVisibility(View.VISIBLE);
            } else {
                viewHolder.binding.waitAmtGenerateArea.setVisibility(View.GONE);
            }

            viewHolder.binding.amtWaitTxtView.setText(item.get("LBL_AMT_GENERATE_PENDING"));

            viewHolder.binding.containView.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(view, position);
                }
            });

        } else if (holder instanceof HeaderViewHolder) {

            final HashMap<String, String> item = list.get(position);
            final HeaderViewHolder viewHolder = (HeaderViewHolder) holder;

            viewHolder.headerBinding.headerTxtView.setText(item.get("vDate"));

        } else {
            FooterViewHolder footerHolder = (FooterViewHolder) holder;
            this.footerHolder = footerHolder;
        }
    }

    @Override
    public int getItemViewType(int position) {
        HashMap<String, String> item = position < list.size() ? list.get(position) : new HashMap<>();
        if (isPositionFooter(position) && isFooterEnabled == true) {
            return TYPE_FOOTER;
        } else if (item.get("TYPE") != null && item.get("TYPE").equalsIgnoreCase("" + TYPE_HEADER)) {
            return TYPE_HEADER;
        }
        return TYPE_ITEM;
    }

    private boolean isPositionFooter(int position) {
        return position == list.size();
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        if (isFooterEnabled == true) {
            return list.size() + 1;
        } else {
            return list.size();
        }
    }

    public void addFooterView() {

        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null) {
            footerHolder.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null)
            footerHolder.progressContainer.setVisibility(View.GONE);
    }

    public interface OnItemClickListener {
        void onItemClickList(View view, int position);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {
        ItemOrderHistoryDesignBinding binding;

        public ViewHolder(ItemOrderHistoryDesignBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    class FooterViewHolder extends RecyclerView.ViewHolder {
        LinearLayout progressContainer;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressContainer = (LinearLayout) itemView.findViewById(R.id.progressContainer);
        }
    }

    class HeaderViewHolder extends RecyclerView.ViewHolder {
        ItemOrderHistoryHeaderDesignBinding headerBinding;

        public HeaderViewHolder(ItemOrderHistoryHeaderDesignBinding binding) {
            super(binding.getRoot());
            this.headerBinding = binding;
        }
    }
}
