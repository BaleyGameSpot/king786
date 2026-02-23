package com.adapter.files;

import android.content.Context;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.fontanalyzer.SystemFont;
import com.buddyverse.providers.R;
import com.general.files.GeneralFunctions;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class FilterDialogListAdapter extends RecyclerView.Adapter<FilterDialogListAdapter.ViewHolder> {

    Context mContext;
    private ArrayList<HashMap<String, String>> listItem;
    CustomDialogListAdapter.OnItemClickList onItemClickList;
    int selectedItemPosition;
    String keyToShowAsTitle;
    int selectedColor;
    int nonSelectedColor;
    GeneralFunctions generalFunc;


    public FilterDialogListAdapter(Context mContext, ArrayList<HashMap<String, String>> listItem, int selectedItemPosition, String keyToShowAsTitle, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.listItem = listItem;
        this.selectedItemPosition = selectedItemPosition;
        this.keyToShowAsTitle = keyToShowAsTitle;
        this.generalFunc = generalFunc;

        selectedColor = mContext.getResources().getColor(R.color.appThemeColor_1);
        nonSelectedColor = Color.parseColor("#686868");
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup viewGroup, int i) {
        View view = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.item_filter_list_design, viewGroup, false);
        return new ViewHolder(view);
    }

    @Override
    public int getItemViewType(int position) {
        return super.getItemViewType(position);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder viewHolder, int i) {

        viewHolder.rowTitleTxtView.setText(listItem.get(i).get(keyToShowAsTitle));
        if (listItem.get(i).get("isShowForward").equalsIgnoreCase("Yes")) {
            if (generalFunc.isRTLmode()) {
                viewHolder.imgCheck.setRotation(180);
            }
            viewHolder.imgCheck.setVisibility(View.VISIBLE);
        } else {
            viewHolder.imgCheck.setVisibility(View.GONE);
        }

        if (selectedItemPosition == i) {
            viewHolder.rowTitleTxtView.setTextColor(selectedColor);
            viewHolder.rowTitleTxtView.setTypeface(SystemFont.FontStyle.BOLD.font);
        } else {
            viewHolder.rowTitleTxtView.setTextColor(nonSelectedColor);
            viewHolder.rowTitleTxtView.setTypeface(SystemFont.FontStyle.REGULAR.font);
        }

        viewHolder.mainArea.setOnClickListener(v -> {
            if (onItemClickList != null) {
                if (listItem.get(i).get("dateRange").equals("range")) {
                    onItemClickList.onItemClick(i);
                } else {
                    if (i != selectedItemPosition) {
                        onItemClickList.onItemClick(i);
                    }
                }

            }
        });
    }

    @Override
    public int getItemCount() {
        return listItem.size();
    }

    public class ViewHolder extends RecyclerView.ViewHolder {
        MTextView rowTitleTxtView;
        ImageView imgCheck;
        LinearLayout mainArea;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            rowTitleTxtView = itemView.findViewById(R.id.rowTitleTxtView);
            imgCheck = itemView.findViewById(R.id.imgCheck);
            mainArea = itemView.findViewById(R.id.mainArea);
        }
    }

    public void setOnItemClickList(CustomDialogListAdapter.OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position);
    }
}
