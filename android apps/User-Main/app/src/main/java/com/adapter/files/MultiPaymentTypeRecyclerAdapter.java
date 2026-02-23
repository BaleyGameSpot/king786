package com.adapter.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.RadioButton;

import androidx.recyclerview.widget.RecyclerView;

import com.general.files.CustomLinearLayoutManager;
import com.general.files.GeneralFunctions;
import com.act.MultiDeliveryThirdPhaseActivity;
import com.buddyverse.main.R;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by tarwindersingh on 06/01/18.
 */

public class MultiPaymentTypeRecyclerAdapter extends RecyclerView.Adapter<MultiPaymentTypeRecyclerAdapter.ViewHolder> implements MultiListViewAdapter.OnItemClickListener {

    GeneralFunctions generalFunc;
    Context mContext;
    MultiDeliveryThirdPhaseActivity mActivity;
    OnTypeSelectListener onTypeSelectListener;
    ArrayList<HashMap<String, String>> paymentTypeList;
    ArrayList<HashMap<String, String>> recipientList;
    int pos;
    private int lastCheckedPosition = -1;
    private int selectedRecipient = -1;
    MultiListViewAdapter adapter;

    String LBL_RECIPIENT_PAY_INST_TXT;
    int maxDestAddAllowedCount;

    public MultiPaymentTypeRecyclerAdapter(MultiDeliveryThirdPhaseActivity mActivity, GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> recipientList, ArrayList<HashMap<String, String>> paymentTypeList, Context mContext, int maxDestAddAllowedCount) {
        this.generalFunc = generalFunc;
        this.paymentTypeList = paymentTypeList;
        this.recipientList = recipientList;
        this.mContext = mContext;
        this.mActivity = mActivity;
        this.maxDestAddAllowedCount = maxDestAddAllowedCount;
        LBL_RECIPIENT_PAY_INST_TXT = generalFunc.retrieveLangLBl("Each recipient has to pay", "LBL_RECIPIENT_PAY_INST_TXT");
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.multi_item_selected_payment_method, parent, false);

        ViewHolder viewHolder = new ViewHolder(view);
        return viewHolder;
    }


    public void setOnTypeSelectListener(OnTypeSelectListener OnTypeSelectListener) {
        this.onTypeSelectListener = OnTypeSelectListener;
    }

    public void setlastCheckedPosition(int pos) {
        this.lastCheckedPosition = pos;
    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final ViewHolder holder, final int position) {

        HashMap<String, String> map = paymentTypeList.get(position);
        holder.typeTitleTxt.setText(map.get("name"));
        if (position == 1 && maxDestAddAllowedCount > 1) {

            if (adapter == null) {
                RecyclerView.LayoutManager layoutManager = new CustomLinearLayoutManager(mActivity);
                holder.paymentDetailArea.setLayoutManager(layoutManager);
                adapter = new MultiListViewAdapter(mActivity, recipientList);
                holder.paymentDetailArea.setAdapter(adapter);
                adapter.setOnItemClickListener(this);
            }

        }
        if (position == paymentTypeList.size() - 1) {
            holder.dividerview.setVisibility(View.GONE);
        }


        holder.radioBtn.setChecked(position == lastCheckedPosition);

        if (holder.radioBtn.isChecked() && position == 1) {
            holder.paymentDetailArea.setVisibility(View.VISIBLE);
        } else {
            holder.paymentDetailArea.setVisibility(View.GONE);
        }

        if (holder.radioBtn.isChecked() && position == 2) {
            holder.typeInstTxt.setVisibility(View.VISIBLE);
            holder.typeInstTxt.setText("" + LBL_RECIPIENT_PAY_INST_TXT + " " + generalFunc.formatNumAsPerCurrency(generalFunc, ((MultiDeliveryThirdPhaseActivity) mActivity).individualFare, ((MultiDeliveryThirdPhaseActivity) mActivity).currencySymbol, true) + " " + generalFunc.retrieveLangLBl("", "LBL_AMOUNT"));
        } else {
            holder.typeInstTxt.setVisibility(View.GONE);
        }


    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {

        return paymentTypeList.size();
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    @Override
    public int getItemViewType(int position) {
        return position;
    }

    @Override
    public void onItemClickList(int parentPos, int position) {
        if (onTypeSelectListener != null) {
            selectedRecipient = position;
            onTypeSelectListener.onTypeSelect(parentPos, position);
        }
    }


    public interface OnTypeSelectListener {
        void onTypeSelect(int position, int recipientPos);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {


        private MTextView typeTitleTxt;
        private MTextView typeInstTxt;
        private LinearLayout mainArea;
        private RadioButton radioBtn;
        View dividerview;
        private RecyclerView paymentDetailArea;
        // private CardView card_view;

        public ViewHolder(View view) {
            super(view);

            dividerview = (View) view.findViewById(R.id.dividerview);
            typeTitleTxt = (MTextView) view.findViewById(R.id.typeTitleTxt);
            typeInstTxt = (MTextView) view.findViewById(R.id.typeInstTxt);
            radioBtn = (RadioButton) view.findViewById(R.id.radioBtn);
            mainArea = (LinearLayout) view.findViewById(R.id.mainArea);
            paymentDetailArea = (RecyclerView) view.findViewById(R.id.paymentDetailArea);

            mainArea.setOnClickListener(view1 -> radioBtn.performClick());
            radioBtn.setOnClickListener(v -> {
                lastCheckedPosition = getAdapterPosition();
                //because of this blinking problem occurs so
                //i have a suggestion to add notifyDataSetChanged();
                //   notifyItemRangeChanged(0, list.length);//blink list problem

                if (onTypeSelectListener != null) {
                    onTypeSelectListener.onTypeSelect(lastCheckedPosition, selectedRecipient);
                }

                if (adapter != null) {
                    adapter.setSelectedParentPos(lastCheckedPosition);

                }

                notifyDataSetChanged();

            });

        }
    }
}
