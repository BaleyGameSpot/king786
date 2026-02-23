package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.providers.R;
import com.view.MTextView;

import java.util.ArrayList;

/**
 * Created by Admin on 09-10-2017.
 */

public class DaySlotAdapter extends RecyclerView.Adapter<DaySlotAdapter.ViewHolder> {

    Context mContext;
    View view;
    public int isSelectedPos = -1;
    setRecentDateSlotClickList setRecentDateSlotClickList;
    ArrayList<String> daylist;
    ArrayList<String> selectedlist;
    ArrayList<String> displaylist;
    public String selectday = "";
    RecyclerView dayslotRecyclerView;
    int screenWidth;

    public DaySlotAdapter(Context context, ArrayList<String> daylist, ArrayList<String> selectedlist, ArrayList<String> displaylist, String selectday, RecyclerView dayslotRecyclerView, int screenWidth) {
        this.mContext = context;
        this.daylist = daylist;
        this.selectedlist = selectedlist;
        this.displaylist = displaylist;
        this.selectday = selectday;
        this.dayslotRecyclerView = dayslotRecyclerView;
        this.screenWidth = screenWidth;
    }

    @Override
    public DaySlotAdapter.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(mContext).inflate(R.layout.item_dayslot_view, parent, false);

        return new DaySlotAdapter.ViewHolder(view);
    }

    @SuppressLint("UseCompatLoadingForColorStateLists")
    @Override
    public void onBindViewHolder(final DaySlotAdapter.ViewHolder holder, final int position) {

        String item = displaylist.get(position);
        String dayItem = daylist.get(position);
        holder.stratTimeTxtView.setText(item);


        if (selectday.equalsIgnoreCase(dayItem)) {
            isSelectedPos = position;
            holder.mainarea.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.appThemeColor_1));
            holder.cardview.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.appThemeColor_1));
            holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.white));

        } else {
            holder.mainarea.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.cardView23ProBG));
            holder.cardview.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.cardView23ProBG));
            holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.black));
        }

        holder.mainarea.setOnClickListener(v -> {
            isSelectedPos = position;
            if (setRecentDateSlotClickList != null) {
                setRecentDateSlotClickList.itemDateSlotLocClick(position);
            }
        });


    }

    @Override
    public int getItemCount() {
        return daylist.size();
    }

    public class ViewHolder extends RecyclerView.ViewHolder {

        MTextView stratTimeTxtView;
        LinearLayout mainarea;
        LinearLayout cardview;

        public ViewHolder(View itemView) {
            super(itemView);

            stratTimeTxtView = (MTextView) itemView.findViewById(R.id.stratTimeTxtView);
            mainarea = (LinearLayout) itemView.findViewById(R.id.mainarea);
            cardview = itemView.findViewById(R.id.cardview);
        }
    }

    public interface setRecentDateSlotClickList {
        void itemDateSlotLocClick(int position);
    }

    public void setOnClickList(setRecentDateSlotClickList setRecentDateSlotClickList) {
        this.setRecentDateSlotClickList = setRecentDateSlotClickList;
    }


}
