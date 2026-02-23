package com.adapter.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.appcompat.widget.AppCompatImageView;
import androidx.recyclerview.widget.RecyclerView;

import com.act.BiddingTaskActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;
import com.view.simpleratingbar.SimpleRatingBar;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 10-08-2016.
 */
public class BiddingReceivedRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list;
    Context mContext;
    boolean isFooterEnabled = false;
    FooterViewHolder footerHolder;
    private OnItemClickListener mItemClickListener;

    public BiddingReceivedRecycleAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
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
            return new FooterViewHolder(v);
        } else {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_bid_received_design, parent, false);
            return new ViewHolder(view);
        }

    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, final int position) {


        if (holder instanceof ViewHolder) {
            final HashMap<String, String> item = list.get(position);
            final ViewHolder viewHolder = (ViewHolder) holder;


            new CreateRoundedView(0, 20, 0, 0, viewHolder.passengerImgView);

            viewHolder.acceptTxt.setText(item.get("LBL_ACCEPT_TXT"));
            viewHolder.declineTxt.setText(item.get("LBL_DECLINE_TXT"));
            viewHolder.offerTxt.setText(item.get("LB_REWISE_OFFER"));

            if (item.get("eStatusMsg") != null && !item.get("eStatusMsg").equalsIgnoreCase("")) {
                viewHolder.statusTxt.setVisibility(View.VISIBLE);
                viewHolder.statusTxt.setText(item.get("eStatusMsg"));
                viewHolder.btnArea.setVisibility(View.GONE);
            }
            if (item.get("showConfirmBtn").equalsIgnoreCase("Yes")) {
                viewHolder.acceptTxt.setText(item.get("LBL_CONFIRM_TXT"));
                viewHolder.acceptTxt.setVisibility(View.VISIBLE);
            } else if (item.get("showAcceptBtn").equalsIgnoreCase("Yes")) {
                viewHolder.acceptTxt.setText(item.get("LBL_ACCEPT_TXT"));
                viewHolder.acceptTxt.setVisibility(View.VISIBLE);
            } else {
                viewHolder.acceptTxt.setVisibility(View.GONE);
            }

            if (item.get("showDeclineBtn").equalsIgnoreCase("Yes")) {
                viewHolder.declineTxt.setVisibility(View.VISIBLE);

            } else {
                viewHolder.declineTxt.setVisibility(View.GONE);
            }
            if (!Utils.checkText(item.get("biddingAmount"))) {
                viewHolder.bidAmountTxt.setVisibility(View.GONE);
            } else {
                viewHolder.bidAmountTxt.setVisibility(View.VISIBLE);
                viewHolder.bidAmountTxt.setText(item.get("biddingAmountTitle") + ": " + generalFunc.convertNumberWithRTL(item.get("biddingAmount")));
            }

            if (!Utils.checkText(item.get("biddingFinalAmount"))) {
                viewHolder.finalAmountTxt.setVisibility(View.GONE);
            } else {
                viewHolder.finalAmountTxt.setVisibility(View.VISIBLE);
                viewHolder.finalAmountTxt.setText(item.get("biddingfinalAmountTitle") + ": " + generalFunc.convertNumberWithRTL(item.get("biddingFinalAmount")));
                viewHolder.btnArea.setVisibility(View.GONE);
            }

            if (!Utils.checkText(item.get("biddingConfirmAmount"))) {
                viewHolder.txtFinalAmountStatus.setVisibility(View.GONE);
            } else {
                viewHolder.txtFinalAmountStatus.setVisibility(View.VISIBLE);
                viewHolder.txtFinalAmountStatus.setText(item.get("biddingfinalAmountTitle") + ": " + generalFunc.convertNumberWithRTL(item.get("biddingConfirmAmount")));
            }

            if (item.get("showReofferBtn").equalsIgnoreCase("Yes")) {
                viewHolder.offerTxt.setVisibility(View.VISIBLE);

            } else {
                viewHolder.offerTxt.setVisibility(View.GONE);
            }
            if (!Utils.checkText(item.get("biddingReofferAmount"))) {
                viewHolder.offerAmountTxt.setVisibility(View.GONE);

            } else {
                viewHolder.offerAmountTxt.setVisibility(View.VISIBLE);
                viewHolder.offerAmountTxt.setText(item.get("biddingReofferAmountTitle") + ": " + generalFunc.convertNumberWithRTL(item.get("biddingReofferAmount")));
            }

            new LoadImage.builder(LoadImage.bind(item.get("vImage").equals("") ? "111" : item.get("vImage")), viewHolder.passengerImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

            viewHolder.ratingBar.setRating(generalFunc.parseFloatValue(0, item.get("vAvgRating")));

            viewHolder.nameTxt.setText(item.get("vName"));
            viewHolder.declineTxt.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(view, position, "decline");
                }
            });

            viewHolder.offerTxt.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(view, position, "offer");
                }
            });
            viewHolder.acceptTxt.setOnClickListener(view -> {

                if (generalFunc.getJsonValueStr("ENABLE_TAX_FOR_BIDDING_SERVICE", ((BiddingTaskActivity) mContext).obj_userProfile).equalsIgnoreCase("Yes")) {

                    if (mItemClickListener != null) {
                        mItemClickListener.onItemClickList(view, position, "accept");
                    }
                } else {
                    final GenerateAlertBox generateAlert = new GenerateAlertBox(mContext);
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        if (btn_id == 1) {
                            if (mItemClickListener != null) {
                                mItemClickListener.onItemClickList(view, position, "accept");
                            }
                        }
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_ACCEPT_BIDDING_TASK_TXT"));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
                    generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
                    generateAlert.showAlertBox();
                }
            });

            viewHolder.chatImg.setOnClickListener(v -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(v, position, "chat");
                }

            });

            String bidAmountDesc = item.get("description_user");
            if (Utils.checkText(bidAmountDesc)) {
                viewHolder.txtBidAmountDesc.setVisibility(View.VISIBLE);
                viewHolder.txtBidAmountDesc.setText("(" + generalFunc.fromHtml(bidAmountDesc) + ")");
                generalFunc.makeTextViewResizable(viewHolder.txtBidAmountDesc, 2, "...\n+ " + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);
            } else {
                viewHolder.txtBidAmountDesc.setVisibility(View.GONE);
            }

            String reOfferAmountDesc = item.get("description_driver");
            if (Utils.checkText(reOfferAmountDesc)) {
                viewHolder.txtReOfferAmountDesc.setVisibility(View.VISIBLE);
                viewHolder.txtReOfferAmountDesc.setText("(" + generalFunc.fromHtml(reOfferAmountDesc) + ")");
                generalFunc.makeTextViewResizable(viewHolder.txtReOfferAmountDesc, 2, "...\n+ " + generalFunc.retrieveLangLBl("", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);
            } else {
                viewHolder.txtReOfferAmountDesc.setVisibility(View.GONE);
            }
        } else {
            this.footerHolder = (FooterViewHolder) holder;
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
        if (footerHolder != null) {
            footerHolder.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            isFooterEnabled = false;
            footerHolder.progressContainer.setVisibility(View.GONE);
        }
    }


    public interface OnItemClickListener {
        void onItemClickList(View v, int position, String type);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {

        private MTextView offerAmountTxt, bidAmountTxt, finalAmountTxt, txtFinalAmountStatus;

        private SimpleRatingBar ratingBar;
        private MTextView nameTxt, statusTxt;
        private SelectableRoundedImageView passengerImgView;
        private MTextView acceptTxt, declineTxt, offerTxt, txtBidAmountDesc, txtReOfferAmountDesc;
        View btnArea;
        AppCompatImageView chatImg;

        public ViewHolder(View view) {
            super(view);

            txtBidAmountDesc = (MTextView) view.findViewById(R.id.txtBidAmountDesc);
            txtReOfferAmountDesc = (MTextView) view.findViewById(R.id.txtReOfferAmountDesc);
            offerAmountTxt = (MTextView) view.findViewById(R.id.offerAmountTxt);
            finalAmountTxt = (MTextView) view.findViewById(R.id.finalAmountTxt);
            txtFinalAmountStatus = (MTextView) view.findViewById(R.id.txtFinalAmountStatus);
            bidAmountTxt = (MTextView) view.findViewById(R.id.bidAmountTxt);

            ratingBar = (SimpleRatingBar) view.findViewById(R.id.ratingBar);
            nameTxt = (MTextView) view.findViewById(R.id.nameTxt);
            chatImg = (AppCompatImageView) view.findViewById(R.id.chatImg);
            statusTxt = (MTextView) view.findViewById(R.id.statusTxt);
            acceptTxt = (MTextView) view.findViewById(R.id.acceptTxt);
            declineTxt = (MTextView) view.findViewById(R.id.declineTxt);
            offerTxt = (MTextView) view.findViewById(R.id.offerTxt);
            passengerImgView = (SelectableRoundedImageView) view.findViewById(R.id.passengerImgView);
            btnArea = view.findViewById(R.id.btnArea);


        }
    }

    static class FooterViewHolder extends RecyclerView.ViewHolder {
        LinearLayout progressContainer;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressContainer = itemView.findViewById(R.id.progressContainer);
        }
    }
}
