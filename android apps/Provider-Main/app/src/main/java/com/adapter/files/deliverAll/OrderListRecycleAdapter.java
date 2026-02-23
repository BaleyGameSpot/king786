package com.adapter.files.deliverAll;

import android.content.Context;
import android.graphics.drawable.Drawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.graphics.drawable.DrawableCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.model.deliverAll.liveTaskListDataModel;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import org.apache.commons.lang3.text.WordUtils;

import java.util.ArrayList;

/**
 * Created by Admin on 09-07-2016.
 */
public class OrderListRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    public GeneralFunctions generalFunc;
    ArrayList<liveTaskListDataModel> list;
    Context mContext;
    boolean isFooterEnabled = false;
    View footerView;
    FooterViewHolder footerHolder;
    private OnItemClickListener mItemClickListener;
    int size = -1;
    int statusBackColor = -1;
    int color = -1;

    public OrderListRecycleAdapter(Context mContext, ArrayList<liveTaskListDataModel> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;

        size = (int) mContext.getResources().getDimension(R.dimen._50sdp);
        statusBackColor = mContext.getResources().getColor(R.color.appThemeColor_1);
        color = mContext.getResources().getColor(R.color.orange);
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
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.live_task_order_list_cell, parent, false);

            return new ViewHolder(view);
        }

    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, final int position) {


        if (holder instanceof ViewHolder) {
            final liveTaskListDataModel item = list.get(position);

            final ViewHolder viewHolder = (ViewHolder) holder;


            viewHolder.orderCellArea.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position, "");
                }
            });

            if (list.get(position).geteBuyAnyService().equalsIgnoreCase("Yes")) {
                viewHolder.callView.setVisibility(View.GONE);
                viewHolder.chatView.setVisibility(View.GONE);
            }

            viewHolder.callView.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position, "Call");
                }
            });
            viewHolder.chatView.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position, "Chat");
                }
            });
            viewHolder.navigateView.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position, "Navigate");
                }
            });

            if (item.getIsForPickup().equalsIgnoreCase("Yes")) {
                viewHolder.orderPhaseTitleTxt.setText(item.getLBL_CURRENT_TASK_TXT());
                Logger.e("OrderNumber", "::" + item.getOrderNumber());
                viewHolder.orderState_numberTxt.setText(item.getLBL_PICKUP() + " #" + generalFunc.convertNumberWithRTL(item.getOrderNumber()));
                viewHolder.placeNameTxt.setText(item.getRestaurantName());
                viewHolder.placeAddressTxt.setText(item.getRestaurantAddress());
                viewHolder.call_navigate_Area.setVisibility(View.VISIBLE);
                if (Utils.checkText(item.getLiveTaskStatus())) {
                    viewHolder.orderPhaseTitleTxt.setText(item.getLiveTaskStatus());
                    viewHolder.orderPhaseTitleTxt.setVisibility(View.VISIBLE);
                } else {
                    viewHolder.orderPhaseTitleTxt.setVisibility(View.GONE);

                }
                if (Utils.checkText(item.getCompanyRating()) && !item.getCompanyRating().equalsIgnoreCase("0") && !item.getCompanyRating().equalsIgnoreCase("0.0")) {
                    viewHolder.ratingAreaLL.setVisibility(View.VISIBLE);
                    viewHolder.ratingTxt.setText(item.getCompanyRating());
                } else {
                    viewHolder.ratingAreaLL.setVisibility(View.GONE);
                }


                String image_url = CommonUtilities.COMPANY_PHOTO_PATH + item.getRestaurantId() + "/" + item.getRestaurantImage();

                new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, image_url, size, size)), viewHolder.storeImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

                viewHolder.iv_display_icon.setVisibility(View.GONE);
                viewHolder.storeImgView.setVisibility(View.VISIBLE);
                if (generalFunc.isRTLmode()) {
                    viewHolder.orderStatusNumberArea.setBackground(mContext.getResources().getDrawable(R.drawable.start_curve_cardview_rtl));
                }


                Drawable buttonDrawable = viewHolder.orderStatusNumberArea.getBackground();

                buttonDrawable = DrawableCompat.wrap(buttonDrawable);
                //the color is a direct color int and not a color resource
                DrawableCompat.setTint(buttonDrawable, statusBackColor);
                viewHolder.orderStatusNumberArea.setBackground(buttonDrawable);


                if (item.getIsCurrentTask().equals("No")) {
                    viewHolder.call_navigate_Area.setVisibility(View.GONE);
                    viewHolder.orderCellArea.setOnClickListener(null);
                }
            } else {
                String pickedFromRes = item.getPickedFromRes();

                if (Utils.checkText(item.getLiveTaskStatus())) {
                    viewHolder.orderPhaseTitleTxt.setText(item.getLiveTaskStatus());
                    viewHolder.orderPhaseTitleTxt.setVisibility(View.VISIBLE);
                } else {
                    viewHolder.orderPhaseTitleTxt.setVisibility(View.GONE);

                }
                String userName = item.getUserName();

                if (Utils.checkText(userName)) {
                    viewHolder.placeNameTxt.setText(WordUtils.capitalizeFully(userName));
                }
                if (Utils.checkText(item.getUserRating()) && !item.getUserRating().equalsIgnoreCase("0") && !item.getUserRating().equalsIgnoreCase("0.0")) {
                    viewHolder.ratingAreaLL.setVisibility(View.VISIBLE);
                    viewHolder.ratingTxt.setText(item.getUserRating());
                } else {
                    viewHolder.ratingAreaLL.setVisibility(View.GONE);
                }
                viewHolder.placeAddressTxt.setText(item.getUserAddress());
                Logger.e("Data", "::" + item.getOrderNumber());
                viewHolder.orderState_numberTxt.setText(item.getLBL_DELIVER() + " #" + generalFunc.convertNumberWithRTL(item.getOrderNumber()));

                String image_url = item.getUserPPicName();

                new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, image_url, size, size)), viewHolder.storeImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

                viewHolder.iv_display_icon.setVisibility(View.GONE);
                viewHolder.storeImgView.setVisibility(View.VISIBLE);
                viewHolder.iv_display_icon.setImageResource(R.drawable.ic_location_new);
                if (generalFunc.isRTLmode()) {
                    viewHolder.orderStatusNumberArea.setBackground(mContext.getResources().getDrawable(R.drawable.start_curve_cardview_rtl));
                }

                Drawable buttonDrawable = viewHolder.orderStatusNumberArea.getBackground();
                buttonDrawable = DrawableCompat.wrap(buttonDrawable);
                //the color is a direct color int and not a color resource
                DrawableCompat.setTint(buttonDrawable, color);
                viewHolder.orderStatusNumberArea.setBackground(buttonDrawable);
                viewHolder.call_navigate_Area.setVisibility(View.GONE);
                if (pickedFromRes.equalsIgnoreCase("No") || (pickedFromRes.equalsIgnoreCase("Yes") && item.getIsPhotoUploaded().equalsIgnoreCase("No") && item.geteBuyAnyService().equalsIgnoreCase("No"))) {
                    viewHolder.orderCellArea.setOnClickListener(null);
                }
            }

            if (item.getGenieOrderType() != null && item.getGenieOrderType().equalsIgnoreCase("Runner")) {
                viewHolder.iv_display_icon.setVisibility(View.VISIBLE);
                viewHolder.storeImgView.setVisibility(View.GONE);
                String image_url = item.getUserPPicName();

                new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, image_url, size, size)), viewHolder.iv_display_icon).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

            }

        } else {
            FooterViewHolder footerHolder = (FooterViewHolder) holder;
            this.footerHolder = footerHolder;
        }


    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled == true) {
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
        if (isFooterEnabled == true) {
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
        void onItemClickList(int position, String pickedFromRes);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {


        private SelectableRoundedImageView iv_display_icon;
        private SelectableRoundedImageView storeImgView;
        private MTextView orderPhaseTitleTxt, placeNameTxt, placeAddressTxt;
        private MTextView orderState_numberTxt, ratingTxt;
        private LinearLayout call_navigate_Area;
        private LinearLayout orderStatusNumberArea;
        private LinearLayout orderCellArea, ratingAreaLL;
        private AppCompatImageView callView, chatView, navigateView;

        public ViewHolder(View view) {
            super(view);

            orderPhaseTitleTxt = (MTextView) view.findViewById(R.id.orderPhaseTitleTxt);
            orderState_numberTxt = (MTextView) view.findViewById(R.id.orderState_numberTxt);
            placeNameTxt = (MTextView) view.findViewById(R.id.placeNameTxt);
            ratingTxt = (MTextView) view.findViewById(R.id.ratingTxt);
            placeAddressTxt = (MTextView) view.findViewById(R.id.placeAddressTxt);
            iv_display_icon = (SelectableRoundedImageView) view.findViewById(R.id.iv_display_icon);
            storeImgView = (SelectableRoundedImageView) view.findViewById(R.id.storeImgView);
            call_navigate_Area = (LinearLayout) view.findViewById(R.id.call_navigate_Area);
            orderCellArea = (LinearLayout) view.findViewById(R.id.orderCellArea);
            orderStatusNumberArea = (LinearLayout) view.findViewById(R.id.orderStatusNumberArea);
            ratingAreaLL = (LinearLayout) view.findViewById(R.id.ratingTxtLL);

            callView = (AppCompatImageView) view.findViewById(R.id.callView);
            chatView = (AppCompatImageView) view.findViewById(R.id.chatView);
            navigateView = (AppCompatImageView) view.findViewById(R.id.navigateView);
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
