package com.dialogs

import android.content.Context
import android.graphics.drawable.Drawable
import android.view.LayoutInflater
import androidx.appcompat.app.AlertDialog
import androidx.core.content.ContextCompat
import com.archit.calendardaterangepicker.customviews.CalendarListener
import com.archit.calendardaterangepicker.customviews.DateRangeCalendarView
import com.general.files.GeneralFunctions
import com.buddyverse.providers.R
import com.utils.LayoutDirection
import com.view.MTextView
import java.util.Calendar

class FilterDateRangeDialog(
    private val actContext: Context,
    private var generalFunc: GeneralFunctions,
    private var registrationDateCal: Calendar
) {

    private var mCalendarViewListener: CalendarViewListener? = null
    private var dateRangeCalView: DateRangeCalendarView? = null
    private var howtoUseDialog: AlertDialog? = null

    var mStartDate: Calendar? = null
    var mEndDate: Calendar? = null

    var isBothDateSelected = false
    var isRangeSelectDefault = true

    fun setCalendarListener(calendarViewListener: CalendarViewListener?) {
        mCalendarViewListener = calendarViewListener
    }

    fun dialogDismiss() {
        howtoUseDialog?.dismiss()
    }

    fun showPreferenceDialog(startCalendar: Calendar, endCalendar: Calendar) {
        isRangeSelectDefault = true
        if (howtoUseDialog?.isShowing == true) {
            return
        }
        mStartDate = startCalendar
        mEndDate = endCalendar
        val builder = AlertDialog.Builder(actContext)
        val inflater =
            actContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE) as LayoutInflater
        val dialogView = inflater.inflate(R.layout.desgin_date_range_picker, null)
        builder.setView(dialogView)

        howtoUseDialog = builder.create()
        howtoUseDialog?.setCancelable(false)
        LayoutDirection.setLayoutDirection(howtoUseDialog)

        dateRangeCalView = dialogView.findViewById(R.id.cdrvCalendar)
        val skipTxtArea = dialogView.findViewById<MTextView>(R.id.skipTxtArea)
        val btnOk = dialogView.findViewById<MTextView>(R.id.btnOk)
        skipTxtArea.text = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT")
        btnOk.text = generalFunc.retrieveLangLBl("OK", "LBL_BTN_OK_GENERAL")
        skipTxtArea.setOnClickListener { howtoUseDialog?.dismiss() }
        btnOk.setOnClickListener {
            if (mCalendarViewListener != null) {
                if (mStartDate!! == startCalendar) {
                    generalFunc.showGeneralMessage(
                        "",
                        generalFunc.retrieveLangLBl("", "LBL_DATE_VALIDATION_TXT")
                    )
                } else if (!isBothDateSelected) {
                    generalFunc.showGeneralMessage(
                        "",
                        generalFunc.retrieveLangLBl("", "LBL_DATE_VALIDATION_TXT")
                    )
                } else {
                    mCalendarViewListener!!.onDateRangeSelected(mStartDate, mEndDate)
                }
            }
        }

        setCalendarData(startCalendar, endCalendar)

        howtoUseDialog?.show()
    }

    private fun setCalendarData(startCalendar: Calendar, endCalendar: Calendar) {


        val endMonth = Calendar.getInstance()
        endMonth[Calendar.DAY_OF_WEEK] = Calendar.SATURDAY
        endMonth.add(Calendar.DATE, 1)
//        endMonth.time = endMonth.time

        dateRangeCalView?.setVisibleMonthRange(registrationDateCal, Calendar.getInstance())
        dateRangeCalView?.setSelectableDateRange(registrationDateCal, Calendar.getInstance())

        val drawableL: Drawable? =
            ContextCompat.getDrawable(actContext, R.drawable.ic_arrow_left)
        val drawableR: Drawable? =
            ContextCompat.getDrawable(actContext, R.drawable.ic_arrow_right_1)
        if (drawableL != null && drawableR != null) {
            if (generalFunc.isRTLmode) {
                dateRangeCalView?.setNavLeftImage(drawableR)
                dateRangeCalView?.setNavRightImage(drawableL)
            } else {
                dateRangeCalView?.setNavLeftImage(drawableL)
                dateRangeCalView?.setNavRightImage(drawableR)
            }

        }

        dateRangeCalView?.setSelectedDateRange(startCalendar, endCalendar)
        dateRangeCalView?.setCurrentMonth(startCalendar)

        dateRangeCalView!!.setCalendarListener(calendarListener)

    }

    private val calendarListener: CalendarListener = object : CalendarListener {
        override fun onFirstDateSelected(startDate: Calendar) {
            mStartDate = startDate
            if (isRangeSelectDefault) {
                onDateRangeSelected(mStartDate!!, mEndDate!!)
                isRangeSelectDefault = false
                isBothDateSelected = true
            } else {
                isBothDateSelected = false
            }
        }

        override fun onDateRangeSelected(startDate: Calendar, endDate: Calendar) {
            mStartDate = startDate
            mEndDate = endDate
            dateRangeCalView!!.setSelectedDateRange(startDate, endDate)
            isBothDateSelected = true
        }
    }

    interface CalendarViewListener {
        fun onDateRangeSelected(startDate: Calendar?, endDate: Calendar?)
    }
}