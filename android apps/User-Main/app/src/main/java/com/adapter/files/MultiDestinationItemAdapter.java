package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.act.MultiDeliverySecondPhaseActivity;
import com.buddyverse.main.R;
import com.model.Multi_Delivery_Data;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;

/**
 * Created by Admin on 09-07-2016.
 */
public class MultiDestinationItemAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    public GeneralFunctions generalFunc;
    ArrayList<Multi_Delivery_Data> list;
    Context mContext;
    boolean isFooterEnabled = false;
    View footerView;
    FooterViewHolder footerHolder;
    MultiDeliverySecondPhaseActivity multiDeliverySecondPhaseActivity;
    private OnItemClickListener mItemClickListener;
    int pos = -1;


    public MultiDestinationItemAdapter(Context mContext, ArrayList<Multi_Delivery_Data> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;
        multiDeliverySecondPhaseActivity = (MultiDeliverySecondPhaseActivity) mContext;
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        multiDeliverySecondPhaseActivity.height = parent.getMeasuredHeight();

        if (viewType == TYPE_FOOTER) {
            View v = LayoutInflater.from(mContext).inflate(R.layout.footer_list, parent, false);
            this.footerView = v;
            return new FooterViewHolder(v);
        } else {
            View view = LayoutInflater.from(mContext).inflate(R.layout.multi_dest_item_layout, parent, false);
            return new ViewHolder(view);
        }

    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, @SuppressLint("RecyclerView") final int position) {


        if (holder instanceof ViewHolder) {
            int listSize = list.size() - 1;

            final Multi_Delivery_Data item = list.get(position);
            final ViewHolder viewHolder = (ViewHolder) holder;
            item.setListPos(position);

            if (item.getIsFromLoc().equalsIgnoreCase("Yes")) {
                viewHolder.toValueTxt.setText(item.getDestAddress());
                viewHolder.removeAdd.setVisibility(View.GONE);
            } else {
                if (pos == -1) {
                    pos = listSize;
                }
                viewHolder.toValueTxt.setHint(item.getHintLable());

                viewHolder.removeAdd.setVisibility(View.VISIBLE);
            }

//            if (position == pos) {
//                viewHolder.toValueTxt.setTextColor(mContext.getResources().getColor(R.color.mspSelected));
//            } else {
//                viewHolder.toValueTxt.setTextColor(mContext.getResources().getColor(R.color.mspUnSelected));
//            }

//            boolean isRtlMode = generalFunc.isRTLmode();
//
//            if (viewHolder.removeAdd.getVisibility() == View.GONE) {
//                int padding1 = viewHolder.mainSelectionArea.getBackground() != null ? (int) mContext.getResources().getDimension(R.dimen._10sdp) : 0;
//                viewHolder.toValueTxt.setPadding(!isRtlMode ? padding1 : 0, 0, !isRtlMode ? 0 : padding1, 0);
//                Logger.d("PADDING_SET", "in 1>> padding1" + padding1 + "\n isRtlMode" + isRtlMode);
//
//            } else {
//                int padding1 = viewHolder.mainSelectionArea.getBackground() != null ? (int) mContext.getResources().getDimension(R.dimen._10sdp) : 0;
//                viewHolder.toValueTxt.setPadding(!isRtlMode ? padding1 : 0, 0, !isRtlMode ? 0 : padding1, 0);
//                Logger.d("PADDING_SET", "in 2>> padding1" + padding1 + "\n isRtlMode" + isRtlMode);
//
//
//            }

            viewHolder.squareImgView.setVisibility(position == 0 || position == listSize ? View.GONE : View.VISIBLE);
            viewHolder.iv_loc_img.setVisibility(position == 0 ? View.VISIBLE : View.GONE);
            viewHolder.iv_round.setVisibility(position == listSize ? View.VISIBLE : View.GONE);
            viewHolder.aboveLine.setVisibility(position == 0 ? View.INVISIBLE : View.VISIBLE);
            viewHolder.lowerLine.setVisibility(position == listSize ? View.INVISIBLE : View.VISIBLE);

            String text = item.getDestAddress();
            boolean destAddressNotBlank = Utils.checkText(text);

            boolean isAddDestAllowed = position == listSize && !multiDeliverySecondPhaseActivity.ALLOW_MULTIPLE_DEST_ADD_KEY.equalsIgnoreCase("No") && listSize < multiDeliverySecondPhaseActivity.maxDestAddAllowedCount;

            viewHolder.iv_add.setVisibility(isAddDestAllowed ? View.VISIBLE : View.GONE);
            viewHolder.iv_remove.setVisibility((!isAddDestAllowed && destAddressNotBlank && position != listSize) ? View.VISIBLE : View.GONE);
            if(viewHolder.iv_add.getVisibility() == View.GONE && viewHolder.iv_remove.getVisibility() == View.GONE){
                viewHolder.removeAdd.setVisibility(View.GONE);
            }

            viewHolder.toValueTxt.setText(text);

            viewHolder.iv_add.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList("Add", position);
                }
            });

            viewHolder.iv_remove.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList("Remove", position);
                }
            });

            viewHolder.mainSelectionArea.setOnClickListener(view -> {
                if (mItemClickListener != null) {


                    if (pos != position) {
                        pos = position;
                        notifyDataSetChanged();
                    } else {
                        mItemClickListener.onItemClickList("Select", position);
                    }
                }
            });

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
        void onItemClickList(String type, int position);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {


        private MTextView toValueTxt;
        private LinearLayout removeAdd;
        private ImageView iv_round, iv_loc_img, iv_add, iv_remove;
        private View squareImgView;
        private View aboveLine, lowerLine;
        private FrameLayout mainSelectionArea;


        public ViewHolder(View view) {
            super(view);

            toValueTxt = (MTextView) view.findViewById(R.id.toValueTxt);
            removeAdd = (LinearLayout) view.findViewById(R.id.removeAdd);
            iv_round = (ImageView) view.findViewById(R.id.iv_round);
            iv_loc_img = (ImageView) view.findViewById(R.id.iv_loc_img);
            iv_add = (ImageView) view.findViewById(R.id.iv_add);
            iv_remove = (ImageView) view.findViewById(R.id.iv_remove);
            squareImgView = (View) view.findViewById(R.id.squareImgView);
            aboveLine = (View) view.findViewById(R.id.aboveLine);
            lowerLine = (View) view.findViewById(R.id.lowerLine);
            mainSelectionArea = (FrameLayout) view.findViewById(R.id.mainSelectionArea);
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
