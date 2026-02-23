package com.adapter.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class MoreInstructionAdapter extends RecyclerView.Adapter<MoreInstructionAdapter.ViewHolder> {

    Context mContext;

    ArrayList<HashMap<String, String>> instructionslit = new ArrayList<>();
    View view;
    boolean showCheckBox = true;
    @NonNull
    private OnItemCheckListener onItemClick;

    public interface OnItemCheckListener {
        void onItemCheck(HashMap<String, String> item);

        void onItemUncheck(HashMap<String, String> item);
    }

    public MoreInstructionAdapter(Context context, ArrayList<HashMap<String, String>> list, @NonNull OnItemCheckListener onItemCheckListener) {
        this.mContext = context;
        this.instructionslit = list;

        this.onItemClick = onItemCheckListener;

    }

    public void showCheckBox(boolean isShow) {
        showCheckBox = isShow;
    }

    @Override
    public MoreInstructionAdapter.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {


        View view = LayoutInflater.from(mContext).inflate(R.layout.item_instructions, parent, false);


        return new MoreInstructionAdapter.ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(MoreInstructionAdapter.ViewHolder holder, final int position) {

        HashMap<String, String> map = instructionslit.get(position);
        String tTitle = map.get("tTitle");
        String tDescription = map.get("tDescription");
        String eContactLess = map.get("eContactLess");


        holder.institle.setText(tTitle);
        holder.inssubtitle.setText(tDescription);

        holder.instructioncb.setVisibility(showCheckBox ? View.VISIBLE : View.GONE);
        if (map.get("isSelect") != null && map.get("isSelect").equalsIgnoreCase("Yes")) {
            holder.instructioncb.setChecked(true);
        } else {
            holder.instructioncb.setChecked(false);
        }
        holder.instructioncb.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                if (buttonView.isChecked()) {
                    if (eContactLess.equalsIgnoreCase("Yes")) {
                        map.put("isSelect", "Yes");
                        onItemClick.onItemCheck(map);
                    } else {
                        onItemClick.onItemCheck(map);
                    }
                } else {
                    onItemClick.onItemUncheck(map);
                }
            }
        });

        holder.instructioncbLayout.setOnClickListener(v -> {
            if (holder.instructioncb.isChecked()) {
                holder.instructioncb.setChecked(false);
            } else {
                holder.instructioncb.setChecked(true);
            }
        });
    }

    @Override
    public int getItemCount() {
        return instructionslit.size();
    }


    public class ViewHolder extends RecyclerView.ViewHolder {

        MTextView institle;
        MTextView inssubtitle;
        CheckBox instructioncb;
        LinearLayout instructioncbLayout;

        public ViewHolder(View itemView) {
            super(itemView);

            institle = (MTextView) itemView.findViewById(R.id.institle);
            inssubtitle = (MTextView) itemView.findViewById(R.id.inssubtitle);
            instructioncb = (CheckBox) itemView.findViewById(R.id.instructioncb);
            instructioncbLayout = (LinearLayout) itemView.findViewById(R.id.instructioncbLayout);

        }
    }

}