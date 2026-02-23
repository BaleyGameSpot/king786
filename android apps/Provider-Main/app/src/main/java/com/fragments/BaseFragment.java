package com.fragments;

import android.view.View;

import androidx.fragment.app.Fragment;

public class BaseFragment extends Fragment {
    protected void onClickView(View view) {
    }

    //add clickListener
    protected void addToClickHandler(View view) {
        view.setOnClickListener(new OnClickHandler());
    }

    protected class OnClickHandler implements View.OnClickListener {
        @Override
        public void onClick(View view) {
            onClickView(view);
        }
    }
}
